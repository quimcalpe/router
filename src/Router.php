<?php
namespace QuimCalpe\Router;

use QuimCalpe\Router\Exception\MethodNotAllowedException;
use QuimCalpe\Router\Exception\RouteNotFoundException;
use QuimCalpe\Router\Route\ParsedRoute;
use QuimCalpe\Router\Route\Route;
use QuimCalpe\Router\Route\RouteProvider;

class Router
{
    private bool $trailing_slash_check = true;
    private array $routes = [];
    private array $route_names = [];
    private array $regexp_map = [
        '/\{([A-Za-z]\w*)\}/' => '(?<$1>[^/]+)',
        '/\{([A-Za-z]\w*):word\}/' => '(?<$1>\w+)',
        '/\{([A-Za-z]\w*):number\}/' => '(?<$1>\d+)',
        '/\{([A-Za-z]\w*):slug\}/' => '(?<$1>[A-Za-z0-9_-]+)',
        '/\{([A-Za-z]\w*):([^}]+)\}/' => '(?<$1>$2)',
        '/\//' => '\/'
    ];
    private array $parsed_regexp = []; // cache de instancia

    /**
     * @param Route[] $routes
     */
    public function __construct(array $routes = [])
    {
        foreach ($routes as $route) {
            $this->add($route);
        }
    }

    public function add(Route $route): void
    {
        $this->addRoute($route->methods(), $route->uri(), $route->handler(), $route->name());
    }

    public function addRoute(array|string $methods, string $uri, string $handler, string $name = ""): void
    {
        if (trim($name) !== "") {
            $this->route_names[$name] = $uri;
        }
        foreach ((array)$methods as $method) {
            $method = strtoupper($method);
            if (!isset($this->routes[$method])) {
                $this->routes[$method] = [];
            }
            $this->routes[$method][$uri] = $handler;
        }
    }

    public function addRouteProvider(RouteProvider $provider): void
    {
        foreach ($provider->routes() as $route) {
            $this->add($route);
        }
    }

    public function addHead(string $uri, string $handler, string $name = ""): void
    {
        $this->addRoute("HEAD", $uri, $handler, $name);
    }

    public function addGet(string $uri, string $handler, string $name = ""): void
    {
        $this->addRoute("GET", $uri, $handler, $name);
    }

    public function addDelete(string $uri, string $handler, string $name = ""): void
    {
        $this->addRoute("DELETE", $uri, $handler, $name);
    }

    public function addOptions(string $uri, string $handler, string $name = ""): void
    {
        $this->addRoute("OPTIONS", $uri, $handler, $name);
    }

    public function addPatch(string $uri, string $handler, string $name = ""): void
    {
        $this->addRoute("PATCH", $uri, $handler, $name);
    }

    public function addPost(string $uri, string $handler, string $name = ""): void
    {
        $this->addRoute("POST", $uri, $handler, $name);
    }

    public function addPut(string $uri, string $handler, string $name = ""): void
    {
        $this->addRoute("PUT", $uri, $handler, $name);
    }

    public function disableTrailingSlashCheck(): void
    {
        $this->trailing_slash_check = false;
    }

    public function addPattern(string $name, string $regexp): void
    {
        $this->regexp_map = ['/\{(\w+):' . $name . '\}/' => '(?<$1>' . $regexp . ')'] + $this->regexp_map;
        $this->parsed_regexp = []; // invalidar cache si se redefine el mapa
    }

    /**
     * Sustituye los placeholders {param} y {param:tipo} de una ruta nombrada por
     * los valores indicados. La sustitución es literal (sin interpretación de
     * metacaracteres regex en pattern ni $1 en replacement).
     */
    public function findURI(string $name, array $parameters = []): ?string
    {
        if (!array_key_exists($name, $this->route_names)) {
            return null;
        }
        $foundUri = $this->route_names[$name];
        foreach ($parameters as $param => $value) {
            $pattern = '/\{' . preg_quote((string)$param, '/') . '(:\w+)?}/';
            $foundUri = preg_replace_callback(
                $pattern,
                static fn (): string => (string)$value,
                $foundUri
            );
        }

        return $foundUri;
    }

    /**
     * @throws MethodNotAllowedException
     * @throws RouteNotFoundException
     */
    public function parse(string $method, string $uri, string $prefix = ""): ParsedRoute
    {
        $uri = trim(explode("?", $uri)[0]);
        if ($prefix !== "" && !str_starts_with($prefix, "/")) {
            $prefix = "/" . $prefix;
        }
        try {
            return $this->findMatches($method, $uri, $prefix);
        } catch (RouteNotFoundException) {
            $allowed_methods = [];
            foreach (array_keys($this->routes) as $available_method) {
                try {
                    $this->findMatches($available_method, $uri, $prefix);
                    $allowed_methods[] = $available_method;
                } catch (RouteNotFoundException) {
                    // not found, skip
                }
            }
            if (count($allowed_methods)) {
                throw new MethodNotAllowedException(implode(", ", $allowed_methods));
            }

            throw new RouteNotFoundException("No route for '{$uri}' found");
        }
    }

    /**
     * @throws RouteNotFoundException
     */
    private function findMatches(string $method, string $uri, string $prefix = ""): ParsedRoute
    {
        $method = strtoupper($method);
        if (!isset($this->routes[$method])) {
            throw new RouteNotFoundException("No route for '{$uri}' found");
        }
        foreach ($this->routes[$method] as $route => $handler) {
            $parsed_regexp = $this->prepareRouteRegexp($prefix . $route);
            $regex = "/^" . $parsed_regexp . ($this->trailing_slash_check ? "" : "\/?") . "$/i";
            if (preg_match($regex, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                return new ParsedRoute($handler, $params);
            }
        }

        throw new RouteNotFoundException("No route for '{$uri}' found");
    }

    private function prepareRouteRegexp(string $route): string
    {
        if (!array_key_exists($route, $this->parsed_regexp)) {
            $this->parsed_regexp[$route] = preg_replace(
                array_keys($this->regexp_map),
                array_values($this->regexp_map),
                $route
            );
        }

        return $this->parsed_regexp[$route];
    }
}
