<?php

namespace App\Core;


class Router {
    private $routes = [];

    public function addRoute($method, $path, $controllerMethod,
        $requiresAuthentication = false) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'controllerMethod' => $controllerMethod,
            'requiresAuthentication' => $requiresAuthentication
        ];
    }

    public function handleRequest($method, $uri, $database) {
        $parsedUrl = parse_url($uri);
        $path = $parsedUrl['path'];

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['path'] === $path) {
                
                // If the route requires authentication and the user is not authenticated, we redirect him to login page
                if(!isset($_SESSION['user_id']) && $route['requiresAuthentication']) {
                    header("Location: /login");
                    exit;
                }

                list($controller, $method) = explode('@', $route['controllerMethod']);
                $controllerInstance = new $controller($database);
                $controllerInstance->{$method}();
                return;
            }
        }

        // Handle 404 - Page Not Found
        http_response_code(404);
        include_once __DIR__ . "/../views/404.php";
        exit;
    }
}
