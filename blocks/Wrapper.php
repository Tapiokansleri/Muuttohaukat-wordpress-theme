<?php
namespace Muuttohaukat\Blocks;


class Wrapper extends \Muuttohaukat\Block {
  public function getDefaultScreen() {
    return [
      'configured' => false,
      'display' => null,

      'margin' => [
        'top' => 0,
        'bottom' => 0,
        'left' => 0,
        'right' => 0,
        'topOnBottom' => true,
        'leftOnRight' => true,
      ],

      'padding' => [
        'top' => 0,
        'bottom' => 0,
        'left' => 0,
        'right' => 0,
        'topOnBottom' => true,
        'leftOnRight' => true,
      ],

      'flex-wrap' => null,
      'flex-shrink' => null,
      'flex-grow' => null,
      // 'basis' => '1/1',
      'width' => 'w-1/1',

      'align' => 'items-stretch',
      'justify' => 'justify-center',
      'direction' => 'flex-col',

      'textAlign' => null,

      'background' => [
        'disableImage' => false,
        'image' => null,
        'size' => 'cover',
        'position' => 'centerCenter',
        'colour' => 'bg-transparent',
      ],

      'textColor' => null,
    ];
  }

  public function getSettings() {
    $parent = parent::getSettings();

    return \Muuttohaukat\params($parent, [
      'category' => 'widgets',
    ]);
  }

  /**
   * Fetches the image for a breakpoint, falling back to smaller breakpoints if large enough image is not available.
   */
  public function findLargestAvailImage($imageArr, $breakpoint = 'sm') {
    $imageSizeMap = [
      '' => 'medium', // This is the fallback if even the smallest size doesn't satisfy breakpoint
      'sm' => 'medium',
      'md' => 'medium',
      'lg' => 'medium_large',
      'xl' => 'large',
      '2xl' => 'extra_large',
    ];

    $sizes = $imageArr['sizes'];
    $image = null;
    $width = null;
    $height = null;

    // SVGs need special treatment or they get their width and height set from the WP image size.
    if (strpos($imageArr['filename'], '.svg')) {
      $breakpoint = null;
    }

    do {
      // If no image is found for the breakpoint, and there is no more
      // breakpoints to try, use the original.
      if (!$breakpoint) {
        $image = $imageArr['url'];
        $width = $imageArr['width'];
        $height = $imageArr['height'];

        break;
      }

      // Try the sizes array, does an image exist for this breakpoint?
      if (isset($sizes[$imageSizeMap[$breakpoint]])) {
        $size = $imageSizeMap[$breakpoint];
        $image = $sizes[$size];
        $width = $sizes[$size . "-width"];
        $height = $sizes[$size . "-height"];
      } else {
        // Reset the breakpoint for a new try.
        $breakpoints = array_keys($imageSizeMap);
        $key = array_search($breakpoint, $breakpoints);
        $breakpoint = $key ? $breakpoints[$key - 1] : null;
      }
    } while (!$image);

    return [
      'width' => $width,
      'height' => $height,
      'url' => $image,
    ];
  }

  public function render($fields, $isPreview = false, $postId = 0) {
    $data = \Muuttohaukat\params(
      array_merge(
        \Muuttohaukat\getDefaultBlockRenderSettings(), [
          // 'prose' => false, // Use Prose block

          'screens' => [
            'sm_screen' => $this->getDefaultScreen(),
            'md_screen' => $this->getDefaultScreen(),
            'lg_screen' => $this->getDefaultScreen(),
            'xl_screen' => $this->getDefaultScreen(),
            '2xl_screen' => $this->getDefaultScreen(),
          ],
        ]
      ),
      $fields);

    // empty("0") returns true, 0 is a valid value
    $empty = function ($data) {
      if ($data == '0') return false;

      return empty($data);
    };

    // Alternative way to map tailwind classes:
    $bgpMap = [
      'leftTop' => 'bg-left-top',
      'centerTop' => 'bg-top',
      'rightTop' => 'bg-right-top',
      'leftCenter' => 'bg-left',
      'centerCenter' => 'bg-center',
      'rightCenter' => 'bg-right',
      'leftBottom' => 'bg-left-bottom',
      'centerBottom' => 'bg-bottom',
      'rightBottom' => 'bg-right-bottom',
    ];



    $classes = [
      'wrapper',
      'flex',
      ' ' // Leave empty space
    ];
    $styles = [];

    if (isset($data['prose']) && $data['prose']) {
      error_log("Wrapper using prose, this should be fixed. " . \Muuttohaukat\currentUrl());
      $classes[] = 'prose';
    }

    // Holds a reference to the prev image so that it isn't necessary to setup
    // the image in every breakpoint while getting proper sizes.
    $previousImage = null;

    foreach ($data['screens'] as $k => $screen) {
      $k = str_replace('_screen', '', $k);
      $breakpoint = $k; // Saving this for later
      $k = $k . ":";

      // Since tailwind is mobile first, it doesn't make much sense
      // to me that you'd want to set more spesific styles
      // in the mobile breakpoint.

      // Emptying $k generates the classes without the sm: modifier.
      // That results in all styles being applied mobile first
      if ($k === 'sm:') {
        $k = '';
      }

      if ($screen && $screen['configured']) {
        if ($isPreview && ($breakpoint === 'lg' || $breakpoint === 'xl' || $breakpoint === '2xl')) {
          // Skip it, these sizes do not trigger properly in admin and just break.
          continue;
        }

        if ($screen['display']) {
          if ($isPreview && $screen['display'] === 'hidden') {
            // Not hiding in admin in order not to lose it
          } else {
            $classes[] = "{$k}$screen[display]";
          }
        }

        $margin = $screen['margin'];

        $classes[] = $margin['leftOnRight']
          ? (!$empty($margin['left']) ? "{$k}mx-$margin[left]" : "")
          : "{$k}ml-$margin[left] {$k}mr-$margin[right]";

        $classes[] = $margin['topOnBottom']
          ? (!$empty($margin['top']) ? "{$k}my-$margin[top]" : "")
          : "{$k}mt-$margin[top] {$k}mb-$margin[bottom]";

        $padding = $screen['padding'];
        $classes[] = $padding['leftOnRight']
          ? (!$empty($padding['left']) ? "{$k}px-$padding[left]" : '')
          : "{$k}pl-$padding[left] {$k}pr-$padding[right]";

        $classes[] = $padding['topOnBottom']
          ? (!$empty($padding['top']) ? "{$k}py-$padding[top]" : '')
          : "{$k}pt-$padding[top] {$k}pb-$padding[bottom]";

        if ($screen['flex-wrap']) {
          $cls = $screen['flex-wrap'];

          $classes[] = "{$k}$cls";
        }

        if ($screen['flex-shrink']) {
          $cls = $screen['flex-shrink'];

          $classes[] = "{$k}$cls";
        }

        if ($screen['flex-grow']) {
          $cls = $screen['flex-grow'];

          $classes[] = "{$k}$cls";
        }

        $classes[] = !$empty($screen['textAlign']) ? "{$k}" . $screen['textAlign'] : "";


        // $classes[] = !$empty($screen['basis']) ? "{$k}w-" . $screen['basis'] : "";

        // In my experience basis doesn't always work, but width has never failed me. That's why I chose width.
        $classes[] = !$empty($screen['width']) ? "{$k}" . $screen['width'] : "";


        $classes[] = !$empty($screen['align']) ? "{$k}" . $screen['align'] : "";
        $classes[] = !$empty($screen['justify']) ? "{$k}" . $screen['justify'] : "";
        $classes[] = !$empty($screen['direction']) ? "{$k}" . $screen['direction'] : "";

        $classes[] = !$empty($screen['background']['colour']) ? "$k" . $screen['background']['colour'] : "";
        $classes[] = !$empty($screen['textColor']) ? "$k" . $screen['textColor'] : "";

        if (!$screen['background']['disableImage']) {
          if ($screen['background']['size']) {
            $classes[] = "{$k}" . $screen['background']['size'];
          }

          if ($screen['background']['position']) {
            $classes[] = "{$k}" . $bgpMap[$screen['background']['position']];
          }

          $hasImage = !empty($screen['background']['image']);

          if ($hasImage || $previousImage) {
            $image = $hasImage ? $screen['background']['image'] : $previousImage;
            $previousImage = $image;

            $v = str_replace(':', '__', $k);
            $classes[] = "{$v}bgSet"; // Indicator that breakpoint has media set

            // \Muuttohaukat\debug($image);
            // \Muuttohaukat\debug($largest);

            $largest = $this->findLargestAvailImage($image, $breakpoint);
            $styles["--{$v}wrapper-background-image"] = "url('$largest[url]')";
            $styles["--{$v}wrapper-min-height"] = "$largest[height]px";
          }
        }
        // This adds an extra line break between sections.
        $classes[] = ' ';
      }
    }

    $classes = array_filter($classes);
    $classes = \Muuttohaukat\className(...$classes);
    $style = \Muuttohaukat\buildStyleString($styles);

    $template = [['core/heading', ['level' => 2, 'content' => 'Add content']]];
    ?>

    <div <?=$classes?> style="<?=$style?>">
    <?php
      echo '<InnerBlocks template="' . esc_attr(wp_json_encode($template)) . '" />';
    ?>
    </div><?php
  }
}
