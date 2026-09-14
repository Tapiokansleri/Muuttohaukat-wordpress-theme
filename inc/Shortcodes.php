<?php

namespace Muuttohaukat;

const CATEGORY_SHORTCODE_REPLACEMENT_READY = true;

/**
 * Register category taxonomy for pages.
 */
add_action('init', function () {
  if (!post_type_supports('page', 'category')) {
    register_taxonomy_for_object_type('category', 'page');
  }
});

/**
 * [query_pages_by_category category="ID"] shortcode.
 * Lists published pages belonging to the given category.
 */
add_shortcode('query_pages_by_category', function ($atts) {
  $atts = shortcode_atts([
    'category' => '',
  ], $atts, 'query_pages_by_category');

  if (empty($atts['category'])) {
    return '';
  }

  $query = new \WP_Query([
    'post_type' => 'page',
    'post_status' => 'publish',
    'category__in' => [(int) $atts['category']],
    'posts_per_page' => -1,
  ]);

  if (!$query->have_posts()) {
    wp_reset_postdata();
    return '<p>' . esc_html__('Ei sivuja tässä kategoriassa.', 'muuttohaukat') . '</p>';
  }

  $output = '<div class="paikkakunnat-container not-prose"><div class="paikkakunnat">';
  $output .= '<h2>' . esc_html__('Paikkakunnat', 'muuttohaukat') . '</h2><ul>';

  while ($query->have_posts()) {
    $query->the_post();
    $output .= '<li><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></li>';
  }

  $output .= '</ul></div></div>';
  wp_reset_postdata();

  return $output;
});

/**
 * Parse a hex color into RGB. Accepts #RGB, #RRGGBB, RGB, or RRGGBB.
 *
 * @return array{0:int,1:int,2:int,hex:string}
 */
function muuttopaivat_parse_color($color) {
  $fallback = [255, 237, 0, 'hex' => 'ffed00'];
  $color = strtolower(ltrim(trim((string) $color), '#'));

  if (preg_match('/^[0-9a-f]{3}$/', $color)) {
    $color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
  }

  if (!preg_match('/^[0-9a-f]{6}$/', $color)) {
    return $fallback;
  }

  return [
    hexdec(substr($color, 0, 2)),
    hexdec(substr($color, 2, 2)),
    hexdec(substr($color, 4, 2)),
    'hex' => $color,
  ];
}

/**
 * Popularity fill (0–1) for a day. Weekends and month turns are busiest;
 * nearer months read stronger. Opacity of the shortcode color carries this.
 */
function muuttopaivat_fill($day, $days_in_month, $iso_dow, $month_index) {
  $base = [0.30, 0.26, 0.13][$month_index];
  $score = $base;

  if ($day === 1) {
    $score += [0.62, 0.34, 0.18][$month_index];
  } elseif ($day === 2) {
    $score += [0.06, 0.03, 0.02][$month_index];
  }

  $from_end = $days_in_month - $day;
  if ($from_end <= 6) {
    $end = [
      [0.58, 0.54, 0.48, 0.36, 0.20, 0.10, 0.06],
      [0.28, 0.24, 0.18, 0.12, 0.08, 0.04, 0.02],
      [0.18, 0.06, 0.03, 0.01, 0.00, 0.00, 0.00],
    ];
    $score += $end[$month_index][$from_end];
  }

  if ($iso_dow === 6 || $iso_dow === 7) {
    $score += [0.10, 0.07, 0.03][$month_index];
  } elseif ($iso_dow === 5) {
    $score += [0.05, 0.03, 0.015][$month_index];
  }

  return min(0.97, max(0.10, $score));
}

/**
 * Background (base color mixed with white) and contrasting text for a fill level.
 * Solid colors keep the text readable on any container background.
 *
 * @param array{0:int,1:int,2:int} $rgb
 * @return array{0:string,1:string}
 */
function muuttopaivat_day_colors($rgb, $opacity) {
  $mixed = [];
  $luminance = 0.0;

  foreach ([0.2126, 0.7152, 0.0722] as $i => $weight) {
    $mixed[$i] = (int) round($rgb[$i] * $opacity + 255 * (1 - $opacity));
    $channel = $mixed[$i] / 255;
    $luminance += $weight * ($channel <= 0.03928 ? $channel / 12.92 : pow(($channel + 0.055) / 1.055, 2.4));
  }

  $bg = sprintf('#%02x%02x%02x', $mixed[0], $mixed[1], $mixed[2]);
  // WCAG relative luminance: white out-contrasts black only below ~0.179.
  $text = $luminance < 0.179 ? '#ffffff' : '#000000';

  return [$bg, $text];
}

function muuttopaivat_month_name(\DateTimeInterface $date) {
  $names = [
    1 => 'Tammikuu',
    2 => 'Helmikuu',
    3 => 'Maaliskuu',
    4 => 'Huhtikuu',
    5 => 'Toukokuu',
    6 => 'Kesäkuu',
    7 => 'Heinäkuu',
    8 => 'Elokuu',
    9 => 'Syyskuu',
    10 => 'Lokakuu',
    11 => 'Marraskuu',
    12 => 'Joulukuu',
  ];

  $locale = function_exists('determine_locale') ? determine_locale() : get_locale();
  if (strpos((string) $locale, 'fi') === 0) {
    return $names[(int) $date->format('n')];
  }

  $localized = function_exists('wp_date')
    ? wp_date('F', $date->getTimestamp(), $date->getTimezone())
    : date_i18n('F', $date->getTimestamp());

  if (is_string($localized) && $localized !== '' && !preg_match('/^\d+$/', $localized)) {
    if (function_exists('mb_convert_case')) {
      return mb_convert_case($localized, MB_CASE_TITLE, 'UTF-8');
    }
    return ucfirst($localized);
  }

  return $names[(int) $date->format('n')];
}

/**
 * [muuttopaivat color="#FFED00" link="/tarjouspyynto/"]
 *
 * Three-month popularity calendar, markup only (no heading, padding or width cap).
 * Leftmost month is always the current one.
 * Bookable days go to `link` with ?date=Y-m-d. Past days are not links.
 * client.js prefills the quote form's Muuttopvm field from ?date= and carries
 * the date from a chooser page (e.g. /tarjouspyynto/) to the form pages below it.
 */
add_shortcode('muuttopaivat', function ($atts) {
  $atts = shortcode_atts([
    'color' => '#FFED00',
    'colour' => '',
    'link' => '',
  ], $atts, 'muuttopaivat');

  $color_raw = $atts['colour'] !== '' ? $atts['colour'] : $atts['color'];
  $rgb = muuttopaivat_parse_color($color_raw);
  $link = trim((string) $atts['link']);

  $tz = function_exists('wp_timezone') ? wp_timezone() : new \DateTimeZone('Europe/Helsinki');
  $today = (new \DateTimeImmutable('now', $tz))->setTime(0, 0, 0);
  $month_start = $today->modify('first day of this month');
  $weekdays = [
    _x('Ma', 'weekday abbreviation', 'muuttohaukat'),
    _x('Ti', 'weekday abbreviation', 'muuttohaukat'),
    _x('Ke', 'weekday abbreviation', 'muuttohaukat'),
    _x('To', 'weekday abbreviation', 'muuttohaukat'),
    _x('Pe', 'weekday abbreviation', 'muuttohaukat'),
    _x('La', 'weekday abbreviation', 'muuttohaukat'),
    _x('Su', 'weekday abbreviation', 'muuttohaukat'),
  ];

  ob_start();
  ?>
  <section class="mh-muuttopaivat not-prose" aria-label="<?php esc_attr_e('Halutuimmat muuttopäivät', 'muuttohaukat'); ?>">
    <div class="mh-muuttopaivat__months">
      <?php for ($month_index = 0; $month_index < 3; $month_index++) :
        $month_date = $month_start->modify('+' . $month_index . ' months');
        $days_in_month = (int) $month_date->format('t');
        $leading = (int) $month_date->format('N') - 1;
        $month_name = muuttopaivat_month_name($month_date);
        $year = (int) $month_date->format('Y');
        $total_cells = $leading + $days_in_month;
        $trailing = (7 - ($total_cells % 7)) % 7;
        ?>
        <div class="mh-muuttopaivat__month">
          <h3 class="mh-muuttopaivat__month-title"><?php echo esc_html($month_name); ?></h3>
          <div class="mh-muuttopaivat__grid" role="group" aria-label="<?php echo esc_attr($month_name . ' ' . $year); ?>">
            <?php foreach ($weekdays as $weekday) : ?>
              <span class="mh-muuttopaivat__weekday" aria-hidden="true"><?php echo esc_html($weekday); ?></span>
            <?php endforeach; ?>

            <?php for ($i = 0; $i < $leading; $i++) : ?>
              <span class="mh-muuttopaivat__day is-empty" aria-hidden="true"></span>
            <?php endfor; ?>

            <?php for ($day = 1; $day <= $days_in_month; $day++) :
              $cell_date = $month_date->setDate((int) $month_date->format('Y'), (int) $month_date->format('n'), $day);
              $ymd = $cell_date->format('Y-m-d');
              $iso_dow = (int) $cell_date->format('N');
              $is_past = $cell_date < $today;
              $is_bookable = !$is_past && $link !== '';
              $aria = sprintf(
                /* translators: 1: day number, 2: month name, 3: year */
                _x('%1$d. %2$s %3$d', 'calendar day accessible name', 'muuttohaukat'),
                $day,
                $month_name,
                $year
              );
              $style = '';
              if ($is_past) {
                // Past days are plain gray (muuttopaivat.css), not popularity-colored.
                $aria .= ' — ' . __('ei valittavissa', 'muuttohaukat');
              } else {
                $fill = muuttopaivat_fill($day, $days_in_month, $iso_dow, $month_index);
                [$bg, $text] = muuttopaivat_day_colors($rgb, $fill);
                $style = 'background-color:' . $bg . ';color:' . $text . ';';
              }
              $class = 'mh-muuttopaivat__day' . ($is_past ? ' is-past' : '') . ($is_bookable ? ' is-bookable' : '');
              if ($is_bookable) :
                $href = add_query_arg('date', $ymd, $link);
                ?>
                <a class="<?php echo esc_attr($class); ?>" href="<?php echo esc_url($href); ?>" style="<?php echo esc_attr($style); ?>" aria-label="<?php echo esc_attr($aria); ?>"><?php echo esc_html((string) $day); ?></a>
              <?php else : ?>
                <span class="<?php echo esc_attr($class); ?>"<?php echo $style !== '' ? ' style="' . esc_attr($style) . '"' : ''; ?> aria-label="<?php echo esc_attr($aria); ?>"<?php echo $is_past ? ' aria-disabled="true"' : ''; ?>><?php echo esc_html((string) $day); ?></span>
              <?php endif;
            endfor; ?>

            <?php for ($i = 0; $i < $trailing; $i++) : ?>
              <span class="mh-muuttopaivat__day is-empty" aria-hidden="true"></span>
            <?php endfor; ?>
          </div>
        </div>
      <?php endfor; ?>
    </div>
  </section>
  <?php

  return (string) ob_get_clean();
});

/**
 * Enqueue shortcode styles.
 */
add_action('wp_enqueue_scripts', function () {
  $themeUri = get_stylesheet_directory_uri();
  $version = wp_get_theme()->get('Version');

  wp_enqueue_style('paikkakunnat', $themeUri . '/assets/css/paikkakunnat.css', ['muuttohaukat-base'], $version);
  wp_enqueue_style('muuttohaukat-muuttopaivat', $themeUri . '/assets/css/muuttopaivat.css', ['muuttohaukat-base'], $version);
});
