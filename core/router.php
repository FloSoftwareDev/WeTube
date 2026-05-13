<?php

/**
 * Router — request dispatcher with optional middleware.
 *
 * Define routes with ->get() / ->post(), optionally chain ->middleware('auth')
 * or ->middleware('admin') to require login or admin role. Then call
 * ->dispatch() to handle the current request.
 */
class Router
{
        public function __construct() {
        echo "<!-- Router loaded -->";
        }
    private array $routes = ['GET' => [], 'POST' => []];
    private ?array $lastRoute = null;  // pointer to the most recently added route

    public function get(string $path, array $handler): self
    {
        return $this->add('GET', $path, $handler);
    }

    public function post(string $path, array $handler): self
    {
        return $this->add('POST', $path, $handler);
    }

    /**
     * Attach middleware to the most recently defined route.
     * Supported: 'auth' (must be logged in), 'admin' (must be admin).
     */
    public function middleware(string $name): self
    {
        if ($this->lastRoute !== null) {
            $this->lastRoute['middleware'][] = $name;
        }
        return $this;
    }

    private function add(string $method, string $path, array $handler): self
    {
        $this->routes[$method][] = [
            'path'       => $path,
            'handler'    => $handler,
            'middleware' => [],
        ];
        // Keep a reference to this route so middleware() can attach to it
        $this->lastRoute = &$this->routes[$method][array_key_last($this->routes[$method])];
        return $this;
    }

    public function dispatch(string $uri, string $method): void
    {
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = preg_replace('#^/WeTube/public#', '', $uri) ?: '/';

        foreach ($this->routes[$method] ?? [] as $route) {
            $pattern = '#^' . preg_replace('#\{([a-z]+)\}#', '(?P<$1>[^/]+)', $route['path']) . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Run middleware before the controller
                foreach ($route['middleware'] as $name) {
                    $this->runMiddleware($name);
                }

                [$class, $action] = $route['handler'];
                $controller = new $class();
                call_user_func_array([$controller, $action], $params);
                return;
            }
        }

        http_response_code(404);
        echo '404 — Page not found';
    }

    private function runMiddleware(string $name): void
    {
        switch ($name) {
            case 'auth':
                if (!AuthService::check()) {
                    header('Location: /WeTube/public/login');
                    exit;
                }
                break;

            case 'admin':
                if (!AuthService::check() || AuthService::role() !== User::ROLE_ADMIN) {
                    http_response_code(403);
                    echo '403 — Forbidden';
                    exit;
                }
                break;
        }
    }
}