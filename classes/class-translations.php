<?php
namespace Muuttohaukat;

/**
 * Internationalisation helper integrated with Polylang.
 *
 * Manages translatable strings and language-prefixed option names.
 *
 * @package Muuttohaukat
 */
class Translations {
  public $languages;
  public $strings = [];

  public function __construct($languageSlugs = ['fi']) {
    $this->languages = $languageSlugs;

    add_action('admin_init', [$this, 'registerStrings']);
  }

  public function getLanguage() {
    if (!function_exists('pll_current_language')) {
      return $this->languages[0];
    }

    return \pll_current_language();
  }

  public function getLanguages() {
    return $this->languages;
  }

  /**
   * Get a registered string by name.
   */
  public function getText(string $name, string $languageSlug = null) {
    if (!function_exists('pll_translate_string')) {
      return $this->strings[$name] ?? $name;
    }

    return \pll_translate_string($this->strings[$name], $languageSlug ?? $this->getLanguage());
  }

  public function getTextSprintf(string $name, array $replacements, $languageSlug = null) {
    $mangled = $this->getText($name, $languageSlug);

    return sprintf($mangled, ...$replacements);
  }

  public function getOptionName(string $x, string $languageSlug = null) {
    $prefix = $languageSlug ?? $this->getLanguage();

    return "{$prefix}_{$x}";
  }

  public function registerString(string $name, string $string) {
    $this->strings[$name] = $string;

    return $this->strings[$name];
  }

  public function registerStrings() {
    if (!function_exists('pll_register_string')) {
      return;
    }

    foreach ($this->strings as $k => $v) {
      pll_register_string($k, $v, 'muuttohaukat', strlen($v) > 60);
    }
  }
}
