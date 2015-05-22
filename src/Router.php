<?php
namespace QuimCalpe\Router;

class Router
{
    private $trailing_slash_check = true;
    private $routes = [];
    private $regexp_map = [
        '/\{([A-Za-z]\w*)\}/' => '(?<$1>[^/]+)',
        '/\{([A-Za-z]\w*):word\}/' => '(?<$1>\w+)',
        '/\{([A-Za-z]\w*):number\}/' => '(?<$1>\d+)',
        '/\{([A-Za-z]\w*):slug\}/' => '(?<$1>[A-Za-z0-9_-]+)',
        '/\//' => '\/'
    ];

    public function __construct(array $routes = [])
    {
        foreach ($routes as $uri => $handler) {
            if (preg_match_all("/^(\[([A-Z|]+)\])?\/?(.*)/i", $uri, $matches, PREG_SET_ORDER)) {
                $methods = explode("|", strtoupper($matches[0][2]));
                if (count($methods) === 1 && trim($methods[0]) === "") {
                    $methods[0] = "GET";
                }
                foreach ($methods as $method) {
                    if (!isset($this->routes[$method])) {
                        $this->routes[$method] = [];
                    }
                    $this->routes[$method]["/".$matches[0][3]] = $handler;
                }
            }
        }
    }

    public function addRoute($methods, $uri, $handler)
    {
        foreach ((array)$methods as $method) {
            $method = strtoupper($method);
            if (!isset($this->routes[$method])) {
                $this->routes[$method] = [];
            }
            $this->routes[$method][$uri] = $handler;
        }
    }

    public function disableTrailingSlashCheck()
    {
        $this->trailing_slash_check = false;
    }

    public function addPattern($name, $regexp)
    {
        $this->regexp_map = ['/\{(\w+):'.$name.'\}/' => '(?<$1>'.$regexp.')'] + $this->regexp_map;
    }

    public function parse($method, $uri, $prefix = "")
    {
        $uri = trim(explode("?", $uri)[0]);
        if ($prefix !== "" && substr($prefix, 0, 1) !== "/") {
            $prefix = "/".$prefix;
        }
        if (!isset($this->routes[strtoupper($method)])) {
            $allowed_methods = [];
            foreach ($this->routes as $available_method => $routes) {
                try {
                    $this->findMatches($available_method, $uri, $prefix);
                    $allowed_methods[] = $available_method;
                } catch (RouteNotFoundException $e) {
                    // not found, skip
                }
            }
            if (count($allowed_methods)) {
                throw new MethodNotAllowedException(implode(", ", $allowed_methods));
            } else {
                throw new RouteNotFoundException("No route for '{$uri}' found");
            }
        }
        return $this->findMatches($method, $uri, $prefix);
    }

    private function findMatches($method, $uri, $prefix = "")
    {
        foreach (array_keys($this->routes[strtoupper($method)]) as $route) {
            $parsed_regexp = preg_replace(array_keys($this->regexp_map), array_values($this->regexp_map), $prefix.$route);
            if (preg_match_all("/^".$parsed_regexp.($this->trailing_slash_check ? "" : "\/?")."$/i", $uri, $matches, PREG_SET_ORDER)) {
                if (count($matches)) {
                    $matches = array_diff_key($matches[0], range(0, count($matches[0])));
                }
                return new ParsedRoute($this->routes[strtoupper($method)][$route], $matches);
            }
        }
        throw new RouteNotFoundException("No route for '{$uri}' found");
    }

}
