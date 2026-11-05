<?php
/**
 * Markdown for agents: a request with "Accept: text/markdown" for a page or post gets the page text as markdown
 * (Content-Type: text/markdown, x-markdown-tokens); every other request keeps getting HTML.
 *
 * - Only published, public singular content and the front page. Password protected content, drafts and anything for
 *   logged in users stay HTML, so markdown never shows what the HTML page would not.
 * - The markdown comes from the post content run through the normal the_content filters, not from the full page, so
 *   the menu, the footer, forms and the calculator's controls are left out.
 * - The same URL has two representations, so the HTML responses say "Vary: Accept" and the markdown response is not
 *   stored by caches (private, no-store).
 * - Any error falls back to the HTML page. Kill switch without a release: wp option update mh_agent_markdown off
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat\AgentMarkdown;

if (!defined('ABSPATH')) {
  exit;
}

/** True when the request asks for text/markdown at least as strongly as for text/html. */
function accepts_markdown(string $accept): bool {
  $accept = strtolower($accept);
  if (strpos($accept, 'text/markdown') === false) {
    return false;
  }
  $weights = [];
  foreach (explode(',', $accept) as $part) {
    $bits = array_map('trim', explode(';', $part));
    $type = array_shift($bits);
    $weight = 1.0;
    foreach ($bits as $bit) {
      if (strpos($bit, 'q=') === 0) {
        $weight = (float) substr($bit, 2);
      }
    }
    $weights[$type] = $weight;
  }
  $markdown = $weights['text/markdown'] ?? 0.0;
  return $markdown > 0 && $markdown >= ($weights['text/html'] ?? 0.0);
}

function render(\WP_Post $post): string {
  $GLOBALS['post'] = $post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- restored below.
  setup_postdata($post);
  $html = (string) apply_filters('the_content', $post->post_content);
  wp_reset_postdata();

  $markdown = (new Converter())->convert($html);
  if ($markdown === '') {
    return '';
  }
  if (stripos($html, '<h1') === false) {
    $markdown = '# ' . trim(wp_strip_all_tags(get_the_title($post))) . "\n\n" . $markdown;
  }
  return $markdown . "\n\n---\n\nSource: " . get_permalink($post) . "\nAgent skills: " . home_url('/.well-known/agent-skills/index.json') . "\n";
}

function maybe_serve(): void {
  if (get_option('mh_agent_markdown', 'on') === 'off') {
    return;
  }
  if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true) || is_user_logged_in()) {
    return;
  }
  if (!(is_singular() || is_front_page())) {
    return;
  }
  $post = get_queried_object();
  if (!($post instanceof \WP_Post) || $post->post_status !== 'publish' || post_password_required($post)) {
    return;
  }

  header('Vary: Accept', false); // the same address answers differently to an agent

  if (!accepts_markdown((string) ($_SERVER['HTTP_ACCEPT'] ?? '')) || get_post_meta($post->ID, '_genesis_noindex', true)) {
    return;
  }
  try {
    $markdown = render($post);
  } catch (\Throwable $e) {
    return;
  }
  if ($markdown === '') {
    return;
  }

  status_header(200);
  header('Content-Type: text/markdown; charset=utf-8');
  header('Cache-Control: private, no-store, max-age=0');
  header('Link: <' . get_permalink($post) . '>; rel="canonical"');
  header('x-markdown-tokens: ' . (int) ceil(mb_strlen($markdown) / 4));
  if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'HEAD') {
    echo $markdown; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markdown text, not HTML.
  }
  exit;
}
add_action('template_redirect', __NAMESPACE__ . '\maybe_serve', 1);

/** A small HTML to markdown converter for post content: headings, paragraphs, lists, tables, links, FAQ details. */
final class Converter {
  private const BLOCKS = [
    'p', 'div', 'section', 'article', 'header', 'footer', 'main', 'aside', 'nav', 'figure', 'figcaption', 'h1', 'h2', 'h3',
    'h4', 'h5', 'h6', 'ul', 'ol', 'li', 'table', 'blockquote', 'details', 'summary', 'pre', 'hr', 'address', 'dl', 'dt', 'dd',
  ];
  private const BREAK = "\u{E000}";

  public function convert(string $html): string {
    if (trim($html) === '') {
      return '';
    }
    $dom = new \DOMDocument();
    $previous = libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="utf-8"?><body>' . $html . '</body>', LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    $body = $dom->getElementsByTagName('body')->item(0);
    if (!$body) {
      return '';
    }

    // Things that are not page text: scripts, forms and their controls, hidden parts and the calculator.
    $xpath = new \DOMXPath($dom);
    $drop = '//script|//style|//noscript|//form|//input|//select|//textarea|//button|//svg|//iframe|//template|//*[@hidden]'
      . '|//*[@aria-hidden="true"]|//*[@data-mhi]|//*[@data-mhi-result]'
      . '|//*[contains(concat(" ", normalize-space(@class), " "), " mh-muuttopaivat ")]'
      . '|//*[contains(concat(" ", normalize-space(@class), " "), " screen-reader-text ")]';
    foreach (iterator_to_array($xpath->query($drop)) as $node) {
      if ($node->parentNode) {
        $node->parentNode->removeChild($node);
      }
    }

    $text = $this->children($body);
    $text = preg_replace('/[ \t]+\n/', "\n", $text);
    $text = preg_replace("/\n{3,}/", "\n\n", (string) $text);
    return trim((string) $text);
  }

  private function squash(string $text): string {
    $text = (string) preg_replace('/[ \t\r\n\x{00A0}]+/u', ' ', $text);
    $text = (string) preg_replace('/ ?' . self::BREAK . ' ?/u', "  \n", $text);
    return trim($text);
  }

  private function url(string $href): string {
    $href = trim($href);
    if ($href === '' || $href[0] === '#' || stripos($href, 'javascript:') === 0) {
      return '';
    }
    return $href[0] === '/' && substr($href, 0, 2) !== '//' ? home_url($href) : $href;
  }

  private function inline(\DOMNode $node): string {
    if ($node instanceof \DOMText) {
      return $node->nodeValue;
    }
    if (!($node instanceof \DOMElement)) {
      return '';
    }
    $tag = strtolower($node->tagName);
    if ($tag === 'br') {
      return self::BREAK;
    }
    $inner = '';
    foreach ($node->childNodes as $child) {
      $inner .= $this->inline($child);
    }
    $trimmed = trim($inner);
    if (in_array($tag, self::BLOCKS, true)) {
      return ' ' . $inner . ' ';
    }
    if ($trimmed === '') {
      return $inner;
    }
    $lead = $inner !== ltrim($inner) ? ' ' : '';
    $trail = $inner !== rtrim($inner) ? ' ' : '';
    switch ($tag) {
      case 'strong':
      case 'b':
        return $lead . '**' . $trimmed . '**' . $trail;
      case 'em':
      case 'i':
        return $lead . '*' . $trimmed . '*' . $trail;
      case 'code':
        return $lead . '`' . $trimmed . '`' . $trail;
      case 'a':
        $href = $this->url((string) $node->getAttribute('href'));
        return $href === '' ? $inner : $lead . '[' . $trimmed . '](' . $href . ')' . $trail;
      default:
        return $inner;
    }
  }

  private function children(\DOMNode $node): string {
    $out = '';
    $inline = '';
    $flush = function () use (&$out, &$inline) {
      $paragraph = $this->squash($inline);
      if ($paragraph !== '') {
        $out .= "\n\n" . $paragraph . "\n\n";
      }
      $inline = '';
    };
    foreach ($node->childNodes as $child) {
      if ($child instanceof \DOMElement && in_array(strtolower($child->tagName), self::BLOCKS, true)) {
        $flush();
        $out .= $this->block($child);
      } else {
        $inline .= $this->inline($child);
      }
    }
    $flush();
    return $out;
  }

  private function block(\DOMElement $el): string {
    $tag = strtolower($el->tagName);
    if (preg_match('/^h([1-6])$/', $tag, $m)) {
      $title = $this->squash($this->inline($el));
      return $title === '' ? '' : "\n\n" . str_repeat('#', (int) $m[1]) . ' ' . $title . "\n\n";
    }
    switch ($tag) {
      case 'p':
        $paragraph = $this->squash($this->inline($el));
        return $paragraph === '' ? '' : "\n\n" . $paragraph . "\n\n";
      case 'ul':
      case 'ol':
        return "\n\n" . $this->items($el, 0) . "\n";
      case 'table':
        return "\n\n" . $this->table($el) . "\n\n";
      case 'blockquote':
        $quote = trim($this->children($el));
        return $quote === '' ? '' : "\n\n> " . str_replace("\n", "\n> ", $quote) . "\n\n";
      case 'details':
        $question = '';
        $answer = '';
        foreach ($el->childNodes as $child) {
          if ($child instanceof \DOMElement && strtolower($child->tagName) === 'summary') {
            $question = $this->squash($this->inline($child));
          } else {
            $answer .= $this->childOutput($child);
          }
        }
        return "\n\n" . ($question !== '' ? '**' . $question . "**\n\n" : '') . trim($answer) . "\n\n";
      case 'hr':
        return "\n\n---\n\n";
      case 'pre':
        return "\n\n```\n" . trim($el->textContent) . "\n```\n\n";
      default:
        return $this->children($el);
    }
  }

  /** One child of a details element, as markdown. */
  private function childOutput(\DOMNode $child): string {
    if ($child instanceof \DOMElement && in_array(strtolower($child->tagName), self::BLOCKS, true)) {
      return $this->block($child);
    }
    $text = $this->squash($this->inline($child));
    return $text === '' ? '' : "\n\n" . $text . "\n\n";
  }

  private function items(\DOMElement $list, int $depth): string {
    $ordered = strtolower($list->tagName) === 'ol';
    $number = 1;
    $out = '';
    foreach ($list->childNodes as $item) {
      if (!($item instanceof \DOMElement) || strtolower($item->tagName) !== 'li') {
        continue;
      }
      $text = '';
      $nested = '';
      foreach ($item->childNodes as $child) {
        if ($child instanceof \DOMElement && in_array(strtolower($child->tagName), ['ul', 'ol'], true)) {
          $nested .= $this->items($child, $depth + 1);
        } else {
          $text .= $this->inline($child);
        }
      }
      $out .= str_repeat('  ', $depth) . ($ordered ? ($number++) . '. ' : '- ') . $this->squash($text) . "\n" . $nested;
    }
    return $out;
  }

  private function table(\DOMElement $table): string {
    $rows = [];
    foreach ($table->getElementsByTagName('tr') as $tr) {
      $cells = [];
      foreach ($tr->childNodes as $cell) {
        if ($cell instanceof \DOMElement && in_array(strtolower($cell->tagName), ['th', 'td'], true)) {
          $cells[] = str_replace('|', '\\|', $this->squash($this->inline($cell)));
        }
      }
      if ($cells) {
        $rows[] = $cells;
      }
    }
    if (!$rows) {
      return '';
    }
    $width = max(array_map('count', $rows));
    $lines = [];
    foreach ($rows as $i => $cells) {
      $cells = array_pad($cells, $width, '');
      $lines[] = '| ' . implode(' | ', $cells) . ' |';
      if ($i === 0) {
        $lines[] = '|' . str_repeat(' --- |', $width);
      }
    }
    return implode("\n", $lines);
  }
}
