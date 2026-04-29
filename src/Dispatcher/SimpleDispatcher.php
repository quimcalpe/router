<?php
namespace QuimCalpe\Router\Dispatcher;

use QuimCalpe\Router\Route\ParsedRoute;
use RuntimeException;

class SimpleDispatcher implements DispatcherInterface
{
    /**
     * @throws RuntimeException
     */
    #[\Override]
    public function handle(ParsedRoute $route): mixed
    {
        $segments = explode("::", $route->controller());
        $controller = $segments[0];
        $action = count($segments) > 1 ? $segments[1] : "index";
        if (method_exists($controller, $action)) {
            return call_user_func_array([new $controller, $action], [$route->params()]);
        }

        throw new RuntimeException("No method {$action} in controller {$segments[0]}");
    }
}
