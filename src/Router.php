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
        '/\{([A-Za-z]\w*):([^}]+)\}/' => '(?<$1>$2)',
        '/\//' => '\/'
    ];
    private static $parsed_regexp = []; // cache

    /**
     * Creates a new Router.
     *
     * @param array $routes
     *      (Optional) Associative array of URIs to handlers.
     *      URIs take the form of [VERB1|VERB2]/[uri] or /[uri] (the latter
     *      assumes a verb of GET). Handlers take the form of
     *      ClassName::methodName or ClassName (in the latter case, a
     *      methodName of index is assumed).
     */
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

    /**
     * Registers a route.
     *
     * @param string|array $methods
     *      Either a single HTTP verb, or an array of verbs.
     * @param string $uri
     *      URI pattern to match for this route.
     * @param string $handler
     *      ClassName::methodName to invoke for this route. If methodName
     *      is not present, a method of 'index' is assumed.
     */
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

    /**
     * Syntactic sugar: registers a HEAD route.
     *
     * @param string $uri
     * @param string $handler
     */
    public function addHead($uri, $handler)
    {
        $this->addRoute("HEAD", $uri, $handler);
    }

    /**
     * Syntactic sugar: registers a GET route.
     *
     * @param string $uri
     * @param string $handler
     */
    public function addGet($uri, $handler)
    {
        $this->addRoute("GET", $uri, $handler);
    }

    /**
     * Syntactic sugar: registers a DELETE route.
     *
     * @param string $uri
     * @param string $handler
     */
    public function addDelete($uri, $handler)
    {
        $this->addRoute("DELETE", $uri, $handler);
    }

    /**
     * Syntactic sugar: registers an OPTIONS route.
     *
     * @param string $uri
     * @param string $handler
     */
    public function addOptions($uri, $handler)
    {
        $this->addRoute("OPTIONS", $uri, $handler);
    }

    /**
     * Syntactic sugar: registers a PATCH route.
     *
     * @param string $uri
     * @param string $handler
     */
    public function addPatch($uri, $handler)
    {
        $this->addRoute("PATCH", $uri, $handler);
    }

    /**
     * Syntactic sugar: registers a POST route.
     *
     * @param string $uri
     * @param string $handler
     */
    public function addPost($uri, $handler)
    {
        $this->addRoute("POST", $uri, $handler);
    }

    /**
     * Syntactic sugar: registers a PUT route.
     *
     * @param string $uri
     * @param string $handler
     */
    public function addPut($uri, $handler)
    {
        $this->addRoute("PUT", $uri, $handler);
    }

    /**
     * Disables distinguishing an extra slash on the end of incoming URIs as a
     * different URL.
     */
    public function disableTrailingSlashCheck()
    {
        $this->trailing_slash_check = false;
    }

    /**
     * Registers a matching pattern for URI parameters.
     *
     * @param string $name
     *      Name which will appear within curly-braces in URI patterns.
     * @param string $regexp
     *      Regexp substitution pattern.
     */
    public function addPattern($name, $regexp)
    {
        $this->regexp_map = ['/\{(\w+):'.$name.'\}/' => '(?<$1>'.$regexp.')'] + $this->regexp_map;
    }

    /**
     * Parses an incoming method and URI, and returns a matching route.
     *
     * @param string $method
     *      HTTP verb to find a match on.
     * @param string $uri
     *      URI pattern to find a match on.
     * @param string $prefix
     *      (Optional) Prefix to prepend to URI path.
     *
     * @return ParsedRoute
     *
     * @throws MethodNotAllowedException
     *      Thrown if a handler is registered for this route, but it is not
     *      configured to handle this verb.
     * @throws RouteNotFoundException
     *      Thrown if there is no handler registered for this route.
     */
    public function parse($method, $uri, $prefix = "")
    {
        $uri = trim(explode("?", $uri)[0]);
        if ($prefix !== "" && substr($prefix, 0, 1) !== "/") {
            $prefix = "/".$prefix;
        }
        try {
            return $this->findMatches($method, $uri, $prefix);
        } catch (RouteNotFoundException $e) {
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
    }

    /**
     * Finds the first matching route for a given method and URI.
     *
     * @param string $method
     *      HTTP verb to find a match on.
     * @param string $uri
     *      URI pattern to find a match on.
     * @param string $prefix
     *      (Optional) Prefix to prepend to URI path.
     *
     * @return ParsedRoute
     *
     * @throws RouteNotFoundException
     */
    private function findMatches($method, $uri, $prefix = "")
    {
        if (isset($this->routes[strtoupper($method)])) {
            foreach (array_keys($this->routes[strtoupper($method)]) as $route) {
                $parsed_regexp = $this->prepareRouteRegexp($prefix.$route);
                if (preg_match_all("/^".$parsed_regexp.($this->trailing_slash_check ? "" : "\/?")."$/i", $uri, $matches, PREG_SET_ORDER)) {
                    if (count($matches)) {
                        $matches = array_diff_key($matches[0], range(0, count($matches[0])));
                    }
                    return new ParsedRoute($this->routes[strtoupper($method)][$route], $matches);
                }
            }
        }
        throw new RouteNotFoundException("No route for '{$uri}' found");
    }

    /**
     * Applies standard regexp patterns to an incoming URI route.
     *
     * @param string $route
     *
     * @return string
     */
    private function prepareRouteRegexp($route)
    {
        if (!array_key_exists($route, self::$parsed_regexp)) {
            self::$parsed_regexp[$route] = preg_replace(array_keys($this->regexp_map), array_values($this->regexp_map), $route);
        }
        return self::$parsed_regexp[$route];
    }
}
