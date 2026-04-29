<?php
namespace QuimCalpe\Router\Route;

class Route
{
    private readonly array $methods;

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
    public function __construct(
        array|string $methods,
        private readonly string $uri,
        private readonly string $handler,
        private readonly string $name = "",
    ) {
        $this->methods = (array)$methods;
    }

    /**
     * @return string[]
     */
    public function methods(): array
    {
        return $this->methods;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function handler(): string
    {
        return $this->handler;
    }

    public function name(): string
    {
        return $this->name;
    }
}
