<?php
namespace QuimCalpe\Router\Dispatcher;

use QuimCalpe\Router\Route\ParsedRoute;
use RuntimeException;

class WildcardDispatcher implements DispatcherInterface
{
    /**
     * @throws RuntimeException
     */
    #[\Override]
    public function handle(ParsedRoute $route): mixed
    {
        $controller = $route->controller();
        $rawParams = $route->params();
        foreach ($rawParams as $param => $value) {
            if (str_contains($controller, "{" . $param . "}")) {
                $controller = str_replace("{".$param."}", ucfirst((string)$value), $controller);
                unset($rawParams[$param]);
            }
        }
        $segments = explode("::", $controller);
        $controller = $segments[0];
        $action = count($segments) > 1 ? $segments[1] : "index";
        if (method_exists($controller, $action)) {
            return call_user_func_array([new $controller, $action], [$rawParams]);
        }

        throw new RuntimeException("No method {$action} in controller {$segments[0]}");
    }
}
