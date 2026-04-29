<?php
namespace QuimCalpe\Router\Dispatcher;

use QuimCalpe\Router\Route\ParsedRoute;
use RuntimeException;

class SimpleDispatcher extends AbstractDispatcher
{
    /**
     * @throws RuntimeException
     */
    #[\Override]
    public function handle(ParsedRoute $route): mixed
    {
        [$class, $action] = $this->resolve($route->controller());

        return $this->invoke($class, $action, [$route->params()]);
    }
}
