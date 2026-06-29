<?php
namespace Muuttohaukat;

/**
 * Singleton application container.
 *
 * Bootstraps translations, templates, and ACF blocks.
 *
 * @package Muuttohaukat
 */
class App {
  public $translations;

  protected $blocks = [];
  protected static $instance;

  public static function init($options = []) {
    if (self::$instance) {
      return self::$instance;
    }

    self::$instance = new App($options);

    return self::$instance;
  }

  /**
   * Get option from ACF options page.
   * Set $languageSlug to false to disable option name lookup.
   */
  public function getOption($x, $languageSlug = null) {
    $optionName = $languageSlug === false ? $x : $this->translations->getOptionName($x, $languageSlug);

    return \get_field($optionName, 'options');
  }

  public function getBlock($name) {
    return $this->blocks[$name];
  }

  public function getBlocks() {
    return $this->blocks;
  }

  private function __construct($options = []) {
    $options = array_merge([
      'blocks' => [],
      'templates' => [],
      'languageSlugs' => function_exists('pll_languages_list') ? pll_languages_list() : ['fi'],
    ], $options);

    $this->translations = new Translations($options['languageSlugs']);

    foreach ($options['templates'] as $template) {
      require_once $template;
    }

    add_action('acf/init', function () use ($options) {
      foreach ($options['blocks'] as $block) {
        require_once $block;

        $className = basename($block, '.php');
        $Class = "\\Muuttohaukat\\Blocks\\$className";

        if (!class_exists($Class)) {
          throw new \Exception("Block $block is invalid");
        }

        $instance = new $Class($this);
        $this->blocks[$instance->getName()] = $instance;
      }
    });
  }
}
