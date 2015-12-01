<?php
namespace QuimCalpe\Router\Dispatcher;

use QuimCalpe\Router\Route\ParsedRoute;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class PSR7Dispatcher implements DispatcherInterface
{
    /**
     * @var ServerRequestInterface
     */
    private $request;

    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     */
    public function __construct(ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @param ParsedRoute $route
     * @return Response
     *
     * @throws RuntimeException
     */
    public function handle(ParsedRoute $route)
    {
        $segments = explode("::", $route->controller());
        $controller = $segments[0];
        $action = count($segments) > 1 ? $segments[1] : "index";
        if (method_exists($controller, $action)) {
            $params = [$this->request, $this->response, $route->params()];
            return call_user_func_array([new $controller(), $action], $params);
        } else {
            throw new RuntimeException("No method {$action} in controller {$segments[0]}");
        }
    }
}
