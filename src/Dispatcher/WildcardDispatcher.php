<?php
namespace QuimCalpe\Router\Dispatcher;

use QuimCalpe\Router\Route\ParsedRoute;
use RuntimeException;

class WildcardDispatcher implements DispatcherInterface
{
    /**
     * @param ParsedRoute $route
     * @return string
     *
     * @throws RuntimeException
     */
    public function handle(ParsedRoute $route)
    {
        $controller = $route->controller();
        $rawParams = $route->params();
        foreach ($rawParams as $param => $value) {
            if (strpos($controller, "{".$param."}") !== false) {
                $controller = str_replace("{".$param."}", ucfirst($value), $controller);
                unset($rawParams[$param]);
            }
        }
        $segments = explode("::", $controller);
        $controller = $segments[0];
        $action = count($segments) > 1 ? $segments[1] : "index";
        if (method_exists($controller, $action)) {
            $params = [$rawParams];
            return call_user_func_array([new $controller, $action], $params);
        } else {
            throw new RuntimeException("No method {$action} in controller {$segments[0]}");
        }
    }
}
