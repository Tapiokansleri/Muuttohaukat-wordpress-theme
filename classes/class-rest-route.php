<?php
namespace Muuttohaukat;

/**
 * Abstract base class for REST API endpoints.
 *
 * Provides endpoint registration with optional transient caching.
 * Subclasses define their routes in the constructor via registerEndpoint().
 *
 * @package Muuttohaukat
 */
abstract class RestRoute extends \WP_REST_Controller {
  /** @var string Base route path segment. */
  protected $route;

  /** @var array<string, array> Registered endpoint definitions. */
  public $routes = [];

  public function __construct(string $ns, string $route) {
    $this->namespace = $ns;
    $this->route = $route;
  }

  public function registerEndpoint(string $path, array $params = [], array $transientify = []) {
    if (empty($params)) {
      throw new \Exception('Parameter error: no parameters provided');
    } else if ($path[0] !== '/') {
      throw new \Exception('Endpoint path should always start with /');
    }

    if (!empty($transientify) && class_exists('\Muuttohaukat\Transientify')) {
      $cb = $params['callback'];
      $route = $this->route;
      $ns = $this->namespace;

      $params['callback'] = function ($request) use (&$cb, $route, $ns, $path, $transientify) {
        $reqParams = $request->get_params();
        $transientify = array_merge([
          'type' => 'REST API',
        ], $transientify);

        $key = "{$ns}/{$route}{$path}/[params=" . md5(json_encode($reqParams)) . "]";
        $transientifier = new Transientify($key, $transientify);

        $missReason = null;
        $data = $transientifier->get(function ($transientify) use (&$cb, &$request) {
          $response = $cb($request);

          if (!is_wp_error($response)) {
            return $transientify->set($response);
          } else {
            return $response;
          }
        }, $missReason);

        $response = rest_ensure_response($data);

        if (!is_wp_error($response)) {
          $response->header('X-Transientify', $missReason ?? 'Hit');
        }

        return $response;
      };
    }

    $this->routes[$path] = $params;
  }

  public function registerRoutes() {
    foreach ($this->routes as $path => $params) {
      register_rest_route($this->namespace, $this->route . $path, $params);
    }
  }
}
