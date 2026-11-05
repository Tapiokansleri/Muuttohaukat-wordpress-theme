<?php
/**
 * Files for AI agents under /.well-known: the agent skills index (Agent Skills Discovery RFC v0.2.0), the SKILL.md
 * files it lists, and the ARD manifest (/.well-known/ai-catalog.json).
 *
 * The skill files live in the theme (agent-skills/<name>/SKILL.md) and are served from here, so they ship with a
 * theme release and the digests in the index always match the bytes served. The files hold only what the site
 * already publishes: no prices, no promises about answer times.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat\AgentDiscovery;

if (!defined('ABSPATH')) {
  exit;
}

/** Skill name => what it does (the description is also written in the SKILL.md front matter). */
function skills(): array {
  return [
    'estimate-moving-price' => 'Estimate the price of a home move in Finland from Muuttohaukat\'s published price list: by apartment size, with or without moving boxes, full service (packing and unpacking) and extras.',
    'request-moving-quote' => 'Prepare a quote request for a home move or a business move with Muuttohaukat: which address to open, which choices can be prefilled, and what a person must still enter and send.',
  ];
}

function skill_file(string $name): string {
  return get_template_directory() . '/agent-skills/' . $name . '/SKILL.md';
}

function skill_url(string $name): string {
  return home_url('/.well-known/agent-skills/' . $name . '/SKILL.md');
}

function skills_index(): array {
  $list = [];
  foreach (skills() as $name => $description) {
    $file = skill_file($name);
    if (!is_readable($file)) {
      continue;
    }
    $list[] = [
      'name' => $name,
      'type' => 'skill-md',
      'description' => $description,
      'url' => skill_url($name),
      'digest' => 'sha256:' . hash_file('sha256', $file),
    ];
  }
  return [
    '$schema' => 'https://schemas.agentskills.io/discovery/0.2.0/schema.json',
    'skills' => $list,
  ];
}

/** ARD manifest: the skills above, so a registry can find them with a question in plain language. */
function ard_manifest(): array {
  $queries = [
    'estimate-moving-price' => [
      'how much does a home move cost in Finland',
      'muuttopalvelun hinta 60 neliön asunnolle',
      'price of a move with moving boxes and packing service',
    ],
    'request-moving-quote' => [
      'ask a moving company in Finland for a quote',
      'tarjouspyyntö kotimuutosta',
      'request a quote for an office move in Finland',
    ],
  ];
  $names = ['estimate-moving-price' => 'Estimate the price of a home move', 'request-moving-quote' => 'Request a moving quote'];
  $entries = [];
  foreach (array_keys(skills()) as $name) {
    if (!is_readable(skill_file($name))) {
      continue;
    }
    $entries[] = [
      'identifier' => 'urn:air:muuttohaukat.com:skill:' . $name,
      'displayName' => $names[$name],
      'type' => 'text/markdown',
      'url' => skill_url($name),
      'representativeQueries' => $queries[$name],
    ];
  }
  return [
    'specVersion' => '1.0',
    'host' => [
      'displayName' => 'Muuttohaukat Oy',
      'identifier' => 'urn:air:muuttohaukat.com:host:muuttohaukat-oy',
    ],
    'entries' => $entries,
  ];
}

function send(string $body, string $type): void {
  status_header(200);
  header('Content-Type: ' . $type);
  header('Access-Control-Allow-Origin: *');
  header('Cache-Control: public, max-age=3600');
  header('X-Content-Type-Options: nosniff');
  if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'HEAD') {
    echo $body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON and the theme's own markdown files.
  }
  exit;
}

function serve(): void {
  if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true)) {
    return;
  }
  $path = (string) wp_parse_url(wp_unslash($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
  if (strpos($path, '/.well-known/') !== 0) {
    return;
  }

  $json = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT;
  if ($path === '/.well-known/agent-skills/index.json') {
    send(wp_json_encode(skills_index(), $json) . "\n", 'application/json; charset=utf-8');
  }
  if ($path === '/.well-known/ai-catalog.json') {
    send(wp_json_encode(ard_manifest(), $json) . "\n", 'application/json; charset=utf-8');
  }
  if (preg_match('#^/\.well-known/agent-skills/([a-z0-9-]+)/SKILL\.md$#', $path, $m) && isset(skills()[$m[1]]) && is_readable(skill_file($m[1]))) {
    send((string) file_get_contents(skill_file($m[1])), 'text/markdown; charset=utf-8');
  }
}
add_action('init', __NAMESPACE__ . '\serve', 1);

/** Tells agents that read the HTML where the ARD manifest is (ARD spec 6.1). */
function head_link(): void {
  echo '<link rel="ai-catalog" href="' . esc_url(home_url('/.well-known/ai-catalog.json')) . '">' . "\n";
}
add_action('wp_head', __NAMESPACE__ . '\head_link', 2);
