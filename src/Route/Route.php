<?php
namespace QuimCalpe\Router\Route;

class Route
{
    private $methods;
    private $uri;
    private $handler;
    private $name;

    /**
     * Creates a new Route.
     *
     * @param array|string $methods
     *      Either a single HTTP verb, or an array of verbs.
     * @param string $uri
     *      URI pattern to match for this route.
     * @param string $handler
     *      ClassName::methodName to invoke for this route. If methodName
     *      is not present, a method of 'index' is assumed.
     * @param string $name
     *      (Optional) An unique name for this route.
     */
    public function __construct($methods, $uri, $handler, $name = null)
    {
        $this->methods = (array)$methods;
        $this->uri = (string)$uri;
        $this->handler = (string)$handler;
        $this->name = $name;
    }

    /**
     * Get methods registered for this route.
     *
     * @return array
     */
    public function methods()
    {
        return $this->methods;
    }

    /**
     * Get the URI defined for this route.
     *
     * @return string
     */
    public function uri()
    {
        return $this->uri;
    }

    /**
     * Get the handler defined for this route.
     *
     * @return string
     */
    public function handler()
    {
        return $this->handler;
    }

    /**
     * Get the name defined for this route, or null if undefined.
     *
     * @return string|null
     */
    public function name()
    {
        return $this->name;
    }
}
