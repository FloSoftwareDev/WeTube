<?php

class Router
{
    private array $routes = ['GET' => [], 'POST' => []];

    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, array $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $uri, string $method): void
    {
        // Strip query string (e.g. /search?q=cats -> /search)
        $uri = parse_url($uri, PHP_URL_PATH);

        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            // Convert /watch/{id} into a regex pattern
            $pattern = '#^' . preg_replace('#\{([a-z]+)\}#', '(?P<$1>[^/]+)', $route) . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                // Pull out named params (e.g. 'id' from {id})
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                [$class, $action] = $handler;
                $controller = new $class();
                call_user_func_array([$controller, $action], $params);
                return;
            }
        }

        // No match → 404
        http_response_code(404);
        echo '404 — Page not found';
    }
}