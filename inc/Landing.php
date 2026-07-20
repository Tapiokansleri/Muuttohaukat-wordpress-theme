<?php
/**
 * Landing page route-calculation helpers and REST endpoint.
 *
 * Powers server-side preset route rendering in landing blocks and the REST
 * endpoint at /wp-json/muuttohaukat/v1/route for the interactive route form.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

/**
 * Whether a post's content includes Muuttohaukat landing section blocks.
 *
 * @param \WP_Post|int|null $post Post object or ID; defaults to current post.
 */
function post_has_landing_blocks($post = null) {
  if (!$post instanceof \WP_Post) {
    $post = get_post($post);
  }
  if (!$post instanceof \WP_Post || $post->post_content === '') {
    return false;
  }

  $content = $post->post_content;

  return strpos($content, 'mh-landing-') !== false
    || strpos($content, 'muuttohaukat/landing-') !== false
    || strpos($content, 'muuttohaukat/route-calculator') !== false;
}

if (!function_exists(__NAMESPACE__ . '\landing_known_city_coords')) :

function landing_known_city_coords($name) {
  static $table = [
    'paimio' => [60.4622, 22.6862],
    'turku' => [60.4518, 22.2666],
    'kaarina' => [60.4081, 22.3700],
    'sauvo' => [60.3631, 22.6967],
    'salo' => [60.3839, 23.1297],
    'naantali' => [60.4669, 22.0264],
    'loimaa' => [60.8458, 23.0367],
    'forssa' => [60.8141, 23.6231],
    'helsinki' => [60.1699, 24.9384],
    'espoo' => [60.2055, 24.6559],
    'vantaa' => [60.2934, 25.0378],
    'hämeenlinna' => [60.9959, 24.4643],
    'pori' => [61.4847, 21.7972],
    'tampere' => [61.4978, 23.7610],
    'jyväskylä' => [62.2415, 25.7209],
    'oulu' => [65.0121, 25.4651],
    'rovaniemi' => [66.5039, 25.7294],
    'kuopio' => [62.8924, 27.6770],
    'lahti' => [60.9827, 25.6612],
    'kotka' => [60.4664, 26.9417],
    'kouvola' => [60.8678, 26.7042],
    'mikkeli' => [61.6886, 27.2723],
    'joensuu' => [62.6010, 29.7636],
    'porvoo' => [60.3953, 25.6651],
    'lappeenranta' => [61.0587, 28.1887],
    'seinäjoki' => [62.7903, 22.8403],
    'vaasa' => [63.0951, 21.6165],
    'halikko' => [60.3683, 23.0533],
    'piikkiö' => [60.4267, 22.5167],
    'raisio' => [60.4858, 22.1697],
    'lieto' => [60.5167, 22.4500],
    'hyvinkää' => [60.6313, 24.8615],
    'riihimäki' => [60.7389, 24.7755],
  ];
  $key = mb_strtolower(trim($name), 'UTF-8');
  return $table[$key] ?? null;
}

function landing_geocode_city($name) {
  if (empty($name)) return null;

  $known = landing_known_city_coords($name);
  if ($known) return $known;

  $cache_key = 'mh_geocode_' . md5(mb_strtolower(trim($name), 'UTF-8'));
  $cached = get_transient($cache_key);
  if ($cached !== false) return $cached ?: null;

  $url = add_query_arg([
    'q'      => $name . ', Finland',
    'format' => 'json',
    'limit'  => 1,
  ], 'https://nominatim.openstreetmap.org/search');

  $response = wp_remote_get($url, [
    'timeout' => 4,
    'headers' => ['User-Agent' => 'Muuttohaukat Landing Page (https://muuttohaukat.fi)'],
  ]);

  if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
    set_transient($cache_key, 0, HOUR_IN_SECONDS);
    return null;
  }

  $data = json_decode(wp_remote_retrieve_body($response), true);
  if (empty($data[0]['lat']) || empty($data[0]['lon'])) {
    set_transient($cache_key, 0, HOUR_IN_SECONDS);
    return null;
  }

  $coords = [(float) $data[0]['lat'], (float) $data[0]['lon']];
  set_transient($cache_key, $coords, MONTH_IN_SECONDS);
  return $coords;
}

function landing_haversine_km(array $a, array $b) {
  $r = 6371.0;
  $lat1 = deg2rad($a[0]); $lat2 = deg2rad($b[0]);
  $dlat = $lat2 - $lat1;
  $dlon = deg2rad($b[1] - $a[1]);
  $h = sin($dlat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dlon / 2) ** 2;
  return $r * 2 * atan2(sqrt($h), sqrt(1 - $h));
}

function landing_format_duration_minutes($minutes) {
  $minutes = max(1, (int) round($minutes));
  if ($minutes < 60) return $minutes . ' min';
  $hours = intdiv($minutes, 60);
  $remaining = $minutes % 60;
  return $remaining > 0
    ? sprintf('%d t %d min', $hours, $remaining)
    : sprintf('%d t', $hours);
}

function landing_calculate_route($from, $to) {
  if (empty($from) || empty($to)) return null;

  $cache_key = 'mh_route_' . md5(mb_strtolower(trim($from) . '|' . trim($to), 'UTF-8'));
  $cached = get_transient($cache_key);
  if ($cached !== false) return $cached ?: null;

  $from_coords = landing_geocode_city($from);
  $to_coords   = landing_geocode_city($to);
  if (!$from_coords || !$to_coords) return null;

  $url = sprintf(
    'https://router.project-osrm.org/route/v1/driving/%F,%F;%F,%F?overview=false',
    $from_coords[1], $from_coords[0],
    $to_coords[1], $to_coords[0]
  );
  $response = wp_remote_get($url, ['timeout' => 4]);

  if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
    $data = json_decode(wp_remote_retrieve_body($response), true);
    if (!empty($data['routes'][0])) {
      $route = $data['routes'][0];
      $result = [
        'km'   => (int) round($route['distance'] / 1000),
        'time' => landing_format_duration_minutes($route['duration'] / 60),
      ];
      set_transient($cache_key, $result, MONTH_IN_SECONDS);
      return $result;
    }
  }

  $crow_km = landing_haversine_km($from_coords, $to_coords);
  $road_km = $crow_km * 1.3;
  $minutes = ($road_km / 75) * 60;
  $result = [
    'km'   => (int) round($road_km),
    'time' => landing_format_duration_minutes($minutes),
  ];
  set_transient($cache_key, $result, DAY_IN_SECONDS);
  return $result;
}

/**
 * Apply a small fixed-window limit to public route lookups.
 *
 * Uses only REMOTE_ADDR so client-controlled forwarding headers cannot be
 * used to bypass the limit. Counts and windows are clamped to stay bounded.
 *
 * @return int Seconds until retry, or zero when allowed.
 */
function landing_route_rate_limit() {
  $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
  if (!filter_var($ip, FILTER_VALIDATE_IP)) {
    $ip = 'unknown';
  }

  $limit = max(1, min(100, (int) apply_filters('muuttohaukat_route_rate_limit', 20)));
  $window = max(10, min(HOUR_IN_SECONDS, (int) apply_filters('muuttohaukat_route_rate_window', MINUTE_IN_SECONDS)));
  $key = 'mh_route_limit_' . md5($ip);
  $now = time();
  $state = get_transient($key);

  if (!is_array($state) || empty($state['reset']) || (int) $state['reset'] <= $now) {
    $state = [
      'count' => 0,
      'reset' => $now + $window,
    ];
  }

  if ((int) $state['count'] >= $limit) {
    return max(1, (int) $state['reset'] - $now);
  }

  $state['count'] = min($limit, (int) $state['count'] + 1);
  set_transient($key, $state, max(1, (int) $state['reset'] - $now));

  return 0;
}

endif;

add_action('rest_api_init', function () {
  register_rest_route('muuttohaukat/v1', '/route', [
    'methods'             => 'GET',
    'permission_callback' => '__return_true',
    'args' => [
      'from' => ['required' => true, 'sanitize_callback' => 'sanitize_text_field'],
      'to'   => ['required' => true, 'sanitize_callback' => 'sanitize_text_field'],
    ],
    'callback' => function (\WP_REST_Request $req) {
      $from = trim((string) $req->get_param('from'));
      $to   = trim((string) $req->get_param('to'));
      if ($from === '' || $to === '') {
        return new \WP_Error('missing_params', __('From and To are required.', 'muuttohaukat'), ['status' => 400]);
      }
      if (mb_strlen($from, 'UTF-8') > 100 || mb_strlen($to, 'UTF-8') > 100) {
        return new \WP_Error('invalid_params', __('Kaupungin nimi on liian pitkä.', 'muuttohaukat'), ['status' => 400]);
      }

      $retry_after = landing_route_rate_limit();
      if ($retry_after > 0) {
        return new \WP_Error(
          'rate_limited',
          __('Liian monta hakua. Yritä hetken kuluttua uudelleen.', 'muuttohaukat'),
          [
            'status'      => 429,
            'retry_after' => $retry_after,
          ]
        );
      }

      $result = landing_calculate_route($from, $to);
      if (!$result) {
        return new \WP_Error('not_found', __('Reittiä ei voitu laskea.', 'muuttohaukat'), ['status' => 404]);
      }
      return rest_ensure_response(array_merge(['from' => $from, 'to' => $to], $result));
    },
  ]);
});
