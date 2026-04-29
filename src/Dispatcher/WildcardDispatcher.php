<?php
namespace QuimCalpe\Router\Dispatcher;

use QuimCalpe\Router\Route\ParsedRoute;
use RuntimeException;

class WildcardDispatcher extends AbstractDispatcher
{
    /**
     * @throws RuntimeException
     */
    #[\Override]
    public function handle(ParsedRoute $route): mixed
    {
        $controller = $route->controller();
        $params = $route->params();
        foreach ($params as $name => $value) {
            $placeholder = "{" . $name . "}";
            if (str_contains($controller, $placeholder)) {
                $controller = str_replace($placeholder, ucfirst((string)$value), $controller);
                unset($params[$name]);
            }
        }
        [$class, $action] = $this->resolve($controller);

        return $this->invoke($class, $action, [$params]);
    }
}
