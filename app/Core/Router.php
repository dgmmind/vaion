<?php
namespace App\Core;

class Router
{
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function get(string $path, callable|array $handler): void
    {
        $this->routes['GET'][$this->normalize($path)] = $handler;
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->routes['POST'][$this->normalize($path)] = $handler;
    }

    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method);
        $path = $this->normalize(parse_url($uri, PHP_URL_PATH) ?: '/');

        // Check for exact match first (static routes)
        if (isset($this->routes[$method][$path])) {
            $this->executeHandler($this->routes[$method][$path]);
            return;
        }

        // Check for dynamic routes with parameters
        foreach ($this->routes[$method] as $route => $handler) {
            $pattern = $this->convertToPattern($route);
            if (preg_match($pattern, $path, $matches)) {
                // Filter out numeric keys, keep only named parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                
                // If handler is callable, call it with named parameters
                if (is_callable($handler)) {
                    call_user_func_array($handler, array_values($params));
                    return;
                }
                
                // If handler is array, create instance and call method with parameters
                if (is_array($handler)) {
                    [$class, $action] = $handler;
                    $instance = new $class();
                    if (method_exists($instance, $action)) {
                        call_user_func_array([$instance, $action], array_values($params));
                        return;
                    }
                }
            }
        }

        // No route found
        http_response_code(404);
        echo '<h1>404 Not Found</h1>';
        echo '<p>The requested URL ' . htmlspecialchars($path) . ' was not found on this server.</p>';
    }

    private function convertToPattern(string $route): string
    {
        // Escape special regex chars
        $pattern = preg_quote($route, '/');
        
        // Replace :param with named capture group
        $pattern = preg_replace('/\\:([a-zA-Z0-9_]+)/', '(?P<$1>[^\/]+)', $pattern);
        
        // Simple pattern for numeric IDs
        $pattern = str_replace('\(\\d+\)', '([0-9]+)', $pattern);
        
        return '/^' . $pattern . '$/';
    }

    private function executeHandler($handler, array $params = []): void
    {
        if (is_array($handler)) {
            [$class, $action] = $handler;
            $instance = new $class();
            if (method_exists($instance, $action)) {
                $instance->$action(...$params);
                return;
            }
        } elseif (is_callable($handler)) {
            $handler(...$params);
            return;
        }
        
        http_response_code(500);
        echo '<h1>500 Internal Server Error</h1>';
        echo '<p>Invalid route handler</p>';
    }

    private function normalize(string $path): string
    {
        if ($path === '') return '/';
        if ($path[0] !== '/') $path = '/' . $path;
        if ($path !== '/' && str_ends_with($path, '/')) $path = rtrim($path, '/');
        return $path;
    }
}
