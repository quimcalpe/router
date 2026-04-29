<?php
namespace QuimCalpe\Router\Dispatcher;

use QuimCalpe\Router\Route\ParsedRoute;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class PSR7Dispatcher implements DispatcherInterface
{
    public function __construct(
        private readonly ServerRequestInterface $request,
        private readonly ResponseInterface $response,
    ) {}

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
            $params = [$this->request, $this->response, $route->params()];
            return call_user_func_array([new $controller(), $action], $params);
        }

        throw new RuntimeException("No method {$action} in controller {$segments[0]}");
    }
}
