<?php
namespace Muuttohaukat;

/**
 * Abstract base class for ACF Gutenberg blocks.
 *
 * Provides registration, transient caching, and preview support.
 *
 * @package Muuttohaukat
 */
abstract class Block {
  protected $name = null;

  public function __construct() {
    $this->register($this->getSettings());
  }

  public function register($data) {
    if (function_exists('acf_register_block_type')) {
      acf_register_block_type($data);
      return true;
    }

    if (function_exists('acf_register_block')) {
      acf_register_block($data);
      return true;
    }

    return false;
  }

  public function getName() {
    if (!$this->name) {
      $this->name = (new \ReflectionClass($this))->getShortName();
    }

    return $this->name;
  }

  public function getSettings() {
    return [
      'title' => $this->getName(),
      'name' => strtolower($this->getName()),
      'render_callback' => [$this, 'print'],
      'mode' => 'preview',
      'align' => 'full',
      'category' => 'design',
      'supports' => [
        'align' => true,
        'mode' => true,
        'multiple' => true,
        'jsx' => true,
      ],
    ];
  }

  public function getTransientSettings($block, $postId) {
    $blockSettings = $this->getSettings();
    $blockId = $block['id'];
    $paged = get_query_var('paged', 1);
    $key = "$blockSettings[name]_{$postId}_{$blockId}_$paged";

    return [
      'key' => $key,
      'options' => [
        'expires' => \MINUTE_IN_SECONDS * 5,
        'type' => 'acf-block',
        'bypassPermissions' => ['edit_posts'],
      ]
    ];
  }

  public function renderPreviewNotice($fields, $postId) {
    // No longer necessary in recent versions of Gutenberg.
  }

  public function print($block, $content = '', $isPreview = false, $postId = 0) {
    $fields = \function_exists('get_fields') ? (\get_fields() ?: []) : [];
    $transientSettings = !$isPreview ? $this->getTransientSettings($block, $postId) : false;
    $fields["__block__"] = $block;

    if ($isPreview) {
      $this->renderPreviewNotice($fields, $postId);
    }

    if (!empty($transientSettings) && class_exists('\Muuttohaukat\Transientify')) {
      $transient = new Transientify($transientSettings['key'], $transientSettings['options']);
      $missReason = null;

      echo $transient->get(function ($transientify) use (&$fields, &$isPreview, &$postId) {
        $html = capture([$this, 'render'], $fields, $isPreview, $postId);

        return $transientify->set($html);
      }, $missReason);

      echo "\n<!-- Block " . $this->getName() . " cache: " . transientResult($missReason) . " -->";
    } else {
      $this->render($fields, $isPreview, $postId);
    }
  }

  abstract public function render($fields, $isPreview = false, $postId = 0);
}
