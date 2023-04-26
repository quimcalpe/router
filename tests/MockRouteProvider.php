<?php
namespace QuimCalpe\Router\Test;

use QuimCalpe\Router\Route\RouteProvider;

class MockRouteProvider implements RouteProvider
{
    private $routes;

    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    public function routes(): array
    {
        return $this->routes;
    }
}
