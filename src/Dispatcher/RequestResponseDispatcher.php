<?php
namespace QuimCalpe\Router\Dispatcher;

use Psr\Container\ContainerInterface;
use QuimCalpe\Router\Route\ParsedRoute;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestResponseDispatcher extends AbstractDispatcher
{
    public function __construct(
        private readonly Request $request,
        ?ContainerInterface $container = null,
    ) {
        parent::__construct($container);
    }

    /**
     * @throws RuntimeException
     */
    #[\Override]
    public function handle(ParsedRoute $route): mixed
    {
        [$class, $action] = $this->resolve($route->controller());

        return $this->invoke($class, $action, [$this->request, new Response(), $route->params()]);
    }
}
