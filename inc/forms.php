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

/**
 * Default Dynamics 365 endpoint base URL (no auth params — set full URL in admin).
 *
 * @return string
 */
function d365_endpoint_default() {
  return 'https://func-muuttohaukat-xrm-prod.azurewebsites.net/api/AddOfferToDynamics';
}

/**
 * Resolve the Dynamics 365 Azure Function endpoint.
 *
 * Priority: Teeman asetukset option → wp-config constant → filter.
 *
 * @return string
 */
function d365_endpoint() {
  $stored = get_option('muuttohaukat_d365_endpoint', '');
  if (is_string($stored) && $stored !== '') {
    return $stored;
  }

  if (defined('MUUTTOHAUKAT_D365_ENDPOINT')) {
    return (string) MUUTTOHAUKAT_D365_ENDPOINT;
  }

  return (string) apply_filters('muuttohaukat_d365_endpoint', '');
}

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
 * Log a D365-related message to PHP error log and theme log storage.
 *
 * @param string $message
 */
function d365_log($message) {
  error_log($message);

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
 */
add_action('wplfAfterSubmission', function ($submission, \WPLF\Form $form) {
  $capturedForms = ['tarjouspyynto-kotimuutto', 'tarjouspyynto-yritysmuutto', 'tilaa-muuttotarvikkeet'];
  $deleteAfter = ['whistleblow-lomake'];
  $email = $submission->getField('Email');

  if ($email && $form->slug !== 'tarjouspyynto-yritysmuutto') {
    $msg = __("Hei!\n\nKiitos yhteydenotostasi.\n\nYstävällisin terveisin, Muuttohaukat", 'muuttohaukat');

    if (!wp_mail($email, __('Vahvistus lomakelähetyksestä', 'muuttohaukat'), $msg)) {
      error_log('[Theme form]: Confirmation email failed');
    }
  }

  if (in_array($form->slug, $deleteAfter)) {
    \libreform()->io->submission->delete($submission);
  }

  if (in_array($form->slug, $capturedForms)) {
    $data = [
      'kind' => 'getSubmission',
      'data' => $submission,
    ];

    $json = wp_json_encode($data);

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
      'blocking'   => true,
      'timeout'    => 5,
      'redirection' => 2,
      'headers'    => [
        'Content-Type' => 'application/json; charset=utf-8',
        'Accept'       => 'application/json',
      ],
      'body'        => $json,
    ]);

    if (\is_wp_error($status)) {
      d365_log('[D365]: Forwarding failed: HTTP request error');
      return;
    }

    $response_code = (int) wp_remote_retrieve_response_code($status);
    if ($response_code < 200 || $response_code >= 300) {
      d365_log(sprintf('[D365]: Forwarding failed: HTTP %d', $response_code));
    } else {
      d365_log(sprintf('[D365]: Forwarding succeeded: HTTP %d', $response_code));
    }
  }
}, 10, 2);

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
