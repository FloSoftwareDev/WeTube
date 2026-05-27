<?php
/**
 * Router — matches an incoming URL to a controller method.
 *
 * Routes are defined in config/routes.php like this:
 *   $router->get('/upload', [VideoController::class, 'upload'], 'auth');
 *
 * The optional third argument is a middleware name. Right now only
 * 'auth' is supported (sends the user to the login page if not logged in).
 *
 * URL placeholders look like /watch/{id} — whatever the user puts there
 * is passed to the controller method as an argument.
 */
class Router
{
    private $routes = [];

    public function get($path, $handler, $middleware = '')
    {
        $this->routes[] = [
            'method'     => 'GET',
            'path'       => $path,
            'handler'    => $handler,
            'middleware' => $middleware,
        ];
    }

    public function post($path, $handler, $middleware = '')
    {
        $this->routes[] = [
            'method'     => 'POST',
            'path'       => $path,
            'handler'    => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch($uri, $method)
    {
        // Remove any query string (?q=...) and the /WeTube/public prefix
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = preg_replace('#^/WeTube/public#', '', $uri);
        if ($uri === '') {
            $uri = '/';
        }

        // Split the URL into pieces, e.g. "/watch/12" -> ["watch", "12"]
        $requestParts = explode('/', trim($uri, '/'));

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $routeParts = explode('/', trim($route['path'], '/'));
            if (count($routeParts) !== count($requestParts)) {
                continue;
            }

            // Compare piece by piece. If a route piece looks like {id},
            // it's a placeholder — store the value and keep checking.
            $params = [];
            $matched = true;
            for ($i = 0; $i < count($routeParts); $i++) {
                if (isset($routeParts[$i][0]) && $routeParts[$i][0] === '{') {
                    $params[] = $requestParts[$i];
                } elseif ($routeParts[$i] !== $requestParts[$i]) {
                    $matched = false;
                    break;
                }
            }
            if (!$matched) {
                continue;
            }

            // 'auth' middleware: must be logged in
            if ($route['middleware'] === 'auth' && !AuthService::check()) {
                header('Location: /WeTube/public/login');
                exit;
            }

            // Build the controller and call its method with the URL parameters
            $class      = $route['handler'][0];
            $action     = $route['handler'][1];
            $controller = new $class();
            $controller->$action(...$params);
            return;
        }

        http_response_code(404);
        echo '404 — Page not found';
    }
}
