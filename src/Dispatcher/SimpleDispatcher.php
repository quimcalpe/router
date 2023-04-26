<?php
namespace QuimCalpe\Router\Dispatcher;

use QuimCalpe\Router\Route\ParsedRoute;
use RuntimeException;

class SimpleDispatcher implements DispatcherInterface
{
    /**
     * @param ParsedRoute $route
     * @return string
     *
     * @throws RuntimeException
     */
    public function handle(ParsedRoute $route): mixed
    {
        $segments = explode("::", $route->controller());
        $controller = $segments[0];
        $action = count($segments) > 1 ? $segments[1] : "index";
        if (method_exists($controller, $action)) {
            $params = [$route->params()];
            return call_user_func_array([new $controller, $action], $params);
        }

        throw new RuntimeException("No method {$action} in controller {$segments[0]}");
    }
}
