<?php
namespace QuimCalpe\Router\Dispatcher;

use QuimCalpe\Router\Route\ParsedRoute;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use RuntimeException;

class RequestResponseDispatcher implements DispatcherInterface
{
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param ParsedRoute $route
     * @return Response
     *
     * @throws RuntimeException
     */
    public function handle(ParsedRoute $route): mixed
    {
        $segments = explode("::", $route->controller());
        $controller = $segments[0];
        $action = count($segments) > 1 ? $segments[1] : "index";
        if (method_exists($controller, $action)) {
            $params = [$this->request, new Response(), $route->params()];
            return call_user_func_array([new $controller(), $action], $params);
        }

        throw new RuntimeException("No method {$action} in controller {$segments[0]}");
    }
}
