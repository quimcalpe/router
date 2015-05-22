<?php
namespace QuimCalpe\Router;

use RuntimeException;

class SimpleDispatcher implements DispatcherInterface
{
    public function handle(ParsedRoute $route)
    {
        $segments = explode("::", $route->controller());
        $controller = $segments[0];
        $action = count($segments) > 1 ? $segments[1] : "index";
        if (method_exists($controller, $action)) {
            $params = [];
            if (count($route->params())) {
                $params[] = $route->params();
            }
            return call_user_func_array([new $controller, $action], $params);
        } else {
            throw new RuntimeException("No method {$action} in controller {$segments[0]}");
        }
    }
}
