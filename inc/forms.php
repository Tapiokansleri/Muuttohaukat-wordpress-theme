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

if (!function_exists('libreform')) {
  return;
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
    return MUUTTOHAUKAT_D365_ENDPOINT;
  }

  return (string) apply_filters('muuttohaukat_d365_endpoint', '');
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
      error_log('Failed to send confirmation email!');
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
    if ($endpoint === '') {
      d365_log('[D365]: Endpoint not configured — set it under Appearance → Teeman asetukset → Muut asetukset');
      return;
    }

    $status = wp_remote_post($endpoint, [
      'blocking' => false,
      'body' => $json,
    ]);

    if (\is_wp_error($status)) {
      d365_log("[D365]: Failed to process {$submission->uuid}, backend down?");
    } else {
      d365_log("[D365]: Processing {$submission->uuid}");
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
