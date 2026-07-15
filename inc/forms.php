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
 * Define MUUTTOHAUKAT_D365_ENDPOINT in wp-config.php (recommended).
 * A filter is available for tests or custom overrides.
 *
 * @return string
 */
function d365_endpoint() {
  if (defined('MUUTTOHAUKAT_D365_ENDPOINT')) {
    return MUUTTOHAUKAT_D365_ENDPOINT;
  }

  return (string) apply_filters('muuttohaukat_d365_endpoint', '');
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
      error_log('[D365]: MUUTTOHAUKAT_D365_ENDPOINT is not defined in wp-config.php');
      return;
    }

    $status = wp_remote_post($endpoint, [
      'blocking' => false,
      'body' => $json,
    ]);

    if (\is_wp_error($status)) {
      error_log("[D365]: Failed to process {$submission->uuid}, backend down?");
    } else {
      error_log("[D365]: Processing {$submission->uuid}");
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
