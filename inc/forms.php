<?php
/**
 * LibreForm (WPLF) integration.
 *
 * Handles form submissions (confirmation emails, D365 forwarding)
 * and maps form slugs to template partials.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const D365_ENDPOINT_OPTION = 'muuttohaukat_d365_endpoint';
const D365_BACKUP_OPTION   = 'muuttohaukat_d365_endpoint_backup';

/**
 * Default Dynamics 365 endpoint base URL (no auth params — set full URL in admin).
 *
 * @return string
 */
function d365_endpoint_default() {
  return 'https://func-muuttohaukat-xrm-prod.azurewebsites.net/api/AddOfferToDynamics';
}

/**
 * Candidate D365 endpoints in priority order (primary is tried first).
 *
 * @return string[]
 */
function d365_endpoint_candidates() {
  $candidates = [];

  $stored = get_option(D365_ENDPOINT_OPTION, '');
  if (is_string($stored) && $stored !== '') {
    $candidates[] = $stored;
  }

  $backup = get_option(D365_BACKUP_OPTION, '');
  if (is_string($backup) && $backup !== '') {
    $candidates[] = $backup;
  }

  if (defined('MUUTTOHAUKAT_D365_ENDPOINT')) {
    $candidates[] = (string) MUUTTOHAUKAT_D365_ENDPOINT;
  }

  $filtered = apply_filters('muuttohaukat_d365_endpoint_candidates', $candidates);
  if (!is_array($filtered)) {
    return $candidates;
  }

  return array_values(array_filter($filtered, function ($candidate) {
    return is_string($candidate) && $candidate !== '';
  }));
}

/**
 * Resolve the Dynamics 365 Azure Function endpoint.
 *
 * Uses the first valid candidate from admin, backup, wp-config, or filters.
 *
 * @return string
 */
function d365_endpoint() {
  foreach (d365_endpoint_candidates() as $candidate) {
    if (d365_endpoint_is_valid($candidate)) {
      return $candidate;
    }
  }

  $fallback = apply_filters('muuttohaukat_d365_endpoint', '');
  return is_string($fallback) ? $fallback : '';
}

/**
 * Persist a known-good endpoint to the backup option.
 *
 * @param string $endpoint Endpoint URL.
 */
function d365_backup_endpoint($endpoint) {
  if (!d365_endpoint_is_valid($endpoint)) {
    return;
  }

  update_option(D365_BACKUP_OPTION, $endpoint, false);
}

/**
 * Restore a wiped primary endpoint from backup or wp-config.
 */
function d365_self_heal_endpoint() {
  $stored = get_option(D365_ENDPOINT_OPTION, '');
  if (d365_endpoint_is_valid($stored)) {
    d365_backup_endpoint($stored);
    return;
  }

  $resolved = d365_endpoint();
  if (!d365_endpoint_is_valid($resolved)) {
    return;
  }

  update_option(D365_ENDPOINT_OPTION, $resolved, false);
  d365_backup_endpoint($resolved);
  d365_log('[D365]: Restored endpoint from backup/wp-config');
}

add_action('init', __NAMESPACE__ . '\\d365_self_heal_endpoint', 1);

/**
 * Check that an endpoint is the complete Azure AddOfferToDynamics URL.
 *
 * @param mixed $endpoint Endpoint URL.
 * @return bool
 */
function d365_endpoint_is_valid($endpoint) {
  if (!is_string($endpoint) || $endpoint === '' || !wp_http_validate_url($endpoint)) {
    return false;
  }

  $parts = wp_parse_url($endpoint);
  if (!is_array($parts)
    || strtolower($parts['scheme'] ?? '') !== 'https'
    || empty($parts['host'])
    || !preg_match('/(?:^|\.)azurewebsites\.net$/i', $parts['host'])
    || rtrim($parts['path'] ?? '', '/') !== '/api/AddOfferToDynamics'
    || isset($parts['user'])
    || isset($parts['pass'])
    || isset($parts['fragment'])
  ) {
    return false;
  }

  $query = [];
  parse_str($parts['query'] ?? '', $query);

  return isset($query['id'], $query['code'])
    && is_scalar($query['id'])
    && is_scalar($query['code'])
    && trim((string) $query['id']) !== ''
    && trim((string) $query['code']) !== '';
}

/**
 * Log a D365-related message to theme log storage and optionally PHP error log.
 *
 * Failures are written to both; successes are not written to PHP error log.
 *
 * @param string $message
 * @param bool   $to_php_error_log When false, only the admin D365 log is updated.
 */
function d365_log($message, $to_php_error_log = true) {
  if ($to_php_error_log) {
    error_log($message);
  }

  $entries = get_option('muuttohaukat_d365_log', []);
  if (!is_array($entries)) {
    $entries = [];
  }

  array_unshift($entries, [
    'time'    => current_time('mysql'),
    'message' => $message,
  ]);

  update_option('muuttohaukat_d365_log', array_slice($entries, 0, 50), false);
}

// Keep endpoint helpers available to the settings screen even if LibreForm is
// temporarily unavailable. Only form hooks depend on the plugin.
if (!function_exists('libreform')) {
  return;
}

/**
 * Form submission handler: send confirmations and forward to D365.
 *
 * D365 is sent non-blocking so a slow Azure Function cannot turn a form POST
 * into a 10–30 s request (and a PHP-FPM slow-log entry).
 */
add_action('wplfAfterSubmission', function ($submission, \WPLF\Form $form) {
  try {
    $capturedForms = ['tarjouspyynto-kotimuutto', 'tarjouspyynto-yritysmuutto', 'tilaa-muuttotarvikkeet'];
    $deleteAfter   = ['whistleblow-lomake'];
    $email         = $submission->getField('Email');

    if ($email && $form->slug !== 'tarjouspyynto-yritysmuutto') {
      $mail_error = null;
      $on_fail    = static function ( $error ) use ( &$mail_error ) {
        $mail_error = $error;
      };

      add_action( 'wp_mail_failed', $on_fail );
      $sent = wp_mail(
        $email,
        __('Vahvistus lomakelähetyksestä', 'muuttohaukat'),
        __("Hei!\n\nKiitos yhteydenotostasi.\n\nYstävällisin terveisin, Muuttohaukat", 'muuttohaukat')
      );
      remove_action( 'wp_mail_failed', $on_fail );

      if ( ! $sent ) {
        $detail = ( $mail_error instanceof \WP_Error ) ? $mail_error->get_error_message() : 'wp_mail returned false';
        error_log( '[Themeform]: Confirmation email failed: ' . $detail );
      }
    }

    if ( in_array( $form->slug, $deleteAfter, true ) ) {
      \libreform()->io->submission->delete($submission);
    }

    if ( ! in_array( $form->slug, $capturedForms, true ) ) {
      return;
    }

    $json = wp_json_encode([
      'kind' => 'getSubmission',
      'data' => $submission,
    ]);

    $endpoint = d365_endpoint();
    if (!d365_endpoint_is_valid($endpoint)) {
      d365_log('[D365]: Forwarding failed: endpoint is missing or invalid');
      return;
    }

    if ($json === false) {
      d365_log('[D365]: Forwarding failed: payload could not be encoded');
      return;
    }

    $status = wp_remote_post($endpoint, [
      // Non-blocking: form UX must not wait on Dynamics.
      'blocking'    => false,
      'timeout'     => 5,
      'redirection' => 2,
      'headers'     => [
        'Content-Type' => 'application/json; charset=utf-8',
        'Accept'       => 'application/json',
      ],
      'body'        => $json,
    ]);

    if (\is_wp_error($status)) {
      d365_log(sprintf('[D365]: Forwarding failed: HTTP request error (%s)', $status->get_error_code()));
    }
  } catch ( \Throwable $e ) {
    error_log( '[Themeform]: Submission side-effects failed: ' . $e->getMessage() );
  }
}, 10, 2);

/**
 * WPLF's submit endpoint catches its own failures and only logs them when
 * WP_DEBUG is on, so a rejected submission leaves nothing in the log but an
 * "Undefined variable $useFallback" warning from its own catch block. Record
 * the actual reason instead — a silently failed submission is a lost D365 lead.
 */
add_filter( 'rest_request_after_callbacks', function ( $response, $handler, $request ) {
	if ( ! ( $request instanceof \WP_REST_Request ) ) {
		return $response;
	}

	if ( strpos( (string) $request->get_route(), '/wplf/v2/submitForm' ) === false ) {
		return $response;
	}

	$form_id = (string) ( $request->get_param( '_formId' ) ?: 'unknown' );

	if ( is_wp_error( $response ) ) {
		d365_log( sprintf(
			'[Themeform]: Submission rejected for form %s (%s): %s',
			$form_id,
			$response->get_error_code(),
			$response->get_error_message()
		) );

		return $response;
	}

	if ( ! ( $response instanceof \WP_REST_Response ) || $response->get_status() < 400 ) {
		return $response;
	}

	$data   = $response->get_data();
	$reason = ( is_array( $data ) && ! empty( $data['error'] ) ) ? (string) $data['error'] : 'no reason reported';

	d365_log( sprintf(
		'[Themeform]: Submission failed for form %s (HTTP %d): %s',
		$form_id,
		$response->get_status(),
		$reason
	) );

	return $response;
}, 10, 3 );

/**
 * LibreForm creates a submissions table only after a form is properly saved.
 * Auto-draft libreform posts never get that table, but WPLF still queries it on
 * `before_delete_post` (WordPress daily auto-draft cleanup) → noisy DB errors.
 *
 * Replace WPLF's handler with a table-exists guard.
 */
function wplf_submissions_table_exists( $form_id ) {
	global $wpdb;

	$form_id = absint( $form_id );
	if ( ! $form_id ) {
		return false;
	}

	$table = $wpdb->prefix . 'wplf_' . $form_id . '_submissions';
	$found = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );

	return is_string( $found ) && $found === $table;
}

/**
 * Safe WPLF before-delete callback: skip missing submissions tables.
 *
 * @param int $post_id
 */
function wplf_safe_before_delete_form( $post_id ) {
	$post_id = absint( $post_id );
	if ( ! $post_id || ! function_exists( 'libreform' ) ) {
		return;
	}

	$post = get_post( $post_id );
	if ( ! $post || $post->post_type !== 'libreform' ) {
		return;
	}

	// Daily auto-draft cleanup: these posts never got a submissions table.
	if ( $post->post_status === 'auto-draft' ) {
		return;
	}

	// Never published / never created a submissions table — nothing to clean up.
	if ( ! wplf_submissions_table_exists( $post_id ) ) {
		return;
	}

	libreform()->beforeDeleteForm( $post_id );
}

add_action( 'init', function () {
	if ( ! function_exists( 'libreform' ) || ! class_exists( '\\WPLF\\Plugin' ) ) {
		return;
	}

	$plugin = libreform();
	remove_action( 'before_delete_post', array( $plugin, 'beforeDeleteForm' ) );
	add_action( 'before_delete_post', __NAMESPACE__ . '\\wplf_safe_before_delete_form' );
}, 20 );

/**
 * Map form slugs to template partials.
 */
add_filter('wplfImportFormTemplate', function ($template, \WPLF\Form $form) {
  switch ($form->slug) {
    case 'tarjouspyynto-kotimuutto':
      return capture('\Muuttohaukat\Templates\FormHomeMove');

    case 'tarjouspyynto-yritysmuutto':
      return capture('\Muuttohaukat\Templates\FormBusinessMove');

    case 'jaa-asiakaskokemus':
      return capture('\Muuttohaukat\Templates\FormCustomerFeedback');

    case 'tilaa-muuttotarvikkeet':
      return capture('\Muuttohaukat\Templates\FormAccessoriesOnly');

    case 'rekry':
      return capture('\Muuttohaukat\Templates\FormRecruitment');
  }

  return $template;
}, 10, 2);
