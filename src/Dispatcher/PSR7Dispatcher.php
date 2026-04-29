<?php
namespace QuimCalpe\Router\Dispatcher;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use QuimCalpe\Router\Route\ParsedRoute;
use RuntimeException;

class PSR7Dispatcher extends AbstractDispatcher
{
    public function __construct(
        private readonly ServerRequestInterface $request,
        private readonly ResponseInterface $response,
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

        return $this->invoke($class, $action, [$this->request, $this->response, $route->params()]);
    }
}
