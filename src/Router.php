<?php
namespace QuimCalpe\Router;

use QuimCalpe\Router\Route\Route;
use QuimCalpe\Router\Route\ParsedRoute;
use QuimCalpe\Router\Exception\MethodNotAllowedException;
use QuimCalpe\Router\Exception\RouteNotFoundException;

class Router
{
    private $trailing_slash_check = true;
    private $routes = [];
    private $route_names = [];
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
     * @param Route[] $routes
     *      (Optional) Array of Route value objects to create.
     *
     * @throws \RunTimeException
     *      Thrown if array not contains Router instances.
     */
    public function __construct(array $routes = [])
    {
        foreach ($routes as $uri => $route) {
            if ($route instanceof Route) {
                $this->add($route);
            } else {
                throw new \RunTimeException("An array of QuimCalpe\Router\Route\Route instances is required since 1.0.0");
            }
        }
    }

    /**
     * Registers a route.
     *
     * @param Route $route
     *      Route Value Object.
     */
    public function add(Route $route)
    {
        $this->addRoute($route->methods(), $route->uri(), $route->handler(), $route->name());
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
     * @param string $name
     *      (Optional) An unique name for this route.
     */
    public function addRoute($methods, $uri, $handler, $name = null)
    {
        if (is_string($name) && trim($name) !== "") {
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

    /**
     * Syntactic sugar: registers a HEAD route.
     *
     * @param string $uri
     * @param string $handler
     * @param string $name
     *      (Optional) An unique name for this route.
     */
    public function addHead($uri, $handler, $name = null)
    {
        $this->addRoute("HEAD", $uri, $handler, $name);
    }

    /**
     * Syntactic sugar: registers a GET route.
     *
     * @param string $uri
     * @param string $handler
     * @param string $name
     *      (Optional) An unique name for this route.
     */
    public function addGet($uri, $handler, $name = null)
    {
        $this->addRoute("GET", $uri, $handler, $name);
    }

    /**
     * Syntactic sugar: registers a DELETE route.
     *
     * @param string $uri
     * @param string $handler
     * @param string $name
     *      (Optional) An unique name for this route.
     */
    public function addDelete($uri, $handler, $name = null)
    {
        $this->addRoute("DELETE", $uri, $handler, $name);
    }

    /**
     * Syntactic sugar: registers an OPTIONS route.
     *
     * @param string $uri
     * @param string $handler
     * @param string $name
     *      (Optional) An unique name for this route.
     */
    public function addOptions($uri, $handler, $name = null)
    {
        $this->addRoute("OPTIONS", $uri, $handler, $name);
    }

    /**
     * Syntactic sugar: registers a PATCH route.
     *
     * @param string $uri
     * @param string $handler
     * @param string $name
     *      (Optional) An unique name for this route.
     */
    public function addPatch($uri, $handler, $name = null)
    {
        $this->addRoute("PATCH", $uri, $handler, $name);
    }

    /**
     * Syntactic sugar: registers a POST route.
     *
     * @param string $uri
     * @param string $handler
     * @param string $name
     *      (Optional) An unique name for this route.
     */
    public function addPost($uri, $handler, $name = null)
    {
        $this->addRoute("POST", $uri, $handler, $name);
    }

    /**
     * Syntactic sugar: registers a PUT route.
     *
     * @param string $uri
     * @param string $handler
     * @param string $name
     *      (Optional) An unique name for this route.
     */
    public function addPut($uri, $handler, $name = null)
    {
        $this->addRoute("PUT", $uri, $handler, $name);
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
     * Finds the uri associated with a given name.
     *
     * @param string $name
     *      Name of the route to find.
     * @param array $parameters
     *      (Optional) Parameters to complete the URI.
     *
     * @return string|null
     */
    public function findURI($name, $parameters = [])
    {
        if (array_key_exists($name, $this->route_names)) {
            $foundUri = $this->route_names[$name];
            // insert provided parameters on his slot
            foreach ($parameters as $parameter => $value) {
                $foundUri = preg_replace("/\{(".$parameter.")(\:\w+)?\}/i", $value, $foundUri);
            }
            return $foundUri;
        }
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
