<?php
namespace Muuttohaukat\Blocks;

/**
 * Leadoo in-page bot block.
 *
 * @package Muuttohaukat
 */
class Leadoo extends \Muuttohaukat\Block {
  public function getSettings() {
    return \Muuttohaukat\params(parent::getSettings(), [
      'category' => 'widgets',
    ]);
  }

  public function render($fields, $isPreview = false, $postId = 0) {
    $data = \Muuttohaukat\params([
      'type' => 'CTA',
    ], $fields);

    $scripts = [
      'Generic'       => 'https://bot.leadoo.com/bot/inpage.js?code=rul5sa5Y#seamless,noscroll,nofocus',
      'CorporateMove' => 'https://bot.leadoo.com/bot/inpage.js?code=C4UfKRmA#seamless,noscroll,nofocus',
      'Rekry'         => 'https://bot.leadoo.com/bot/inpage.js?code=vBLZioC4#seamless,noscroll,nofocus',
      'EasyMove'      => 'https://bot.leadoo.com/bot/inpage.js?code=0hmvBKU4#seamless,noscroll,nofocus',
      'Kotimuutto'    => 'https://bot.leadoo.com/bot/inpage.js?code=0hmvBKU4#seamless,nofocus,noscroll',
      'Yritysmuutto'  => 'https://bot.leadoo.com/bot/inpage.js?code=C4UfKRmA#seamless,nofocus,noscroll',
      'CTA'           => 'https://bot.leadoo.com/bot/inpage.js?code=w6ldfWuv#seamless,noscroll,nofocus',
    ];

    $type = isset($scripts[$data['type']]) ? $data['type'] : 'CTA';
    ?>
    <div class="leadoo">
      <?php if ($isPreview) : ?>
        <p><?php esc_html_e('Leadoo does not work in admin.', 'muuttohaukat'); ?></p>
      <?php else : ?>
        <div class="react-root leadoo-root" data-script="<?php echo esc_url($scripts[$type]); ?>"></div>
      <?php endif; ?>
    </div>
    <?php
  }
}
