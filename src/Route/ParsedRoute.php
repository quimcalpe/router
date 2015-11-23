<?php
namespace QuimCalpe\Router\Route;

class ParsedRoute
{
    private $controller;
    private $params;

    public function __construct($controller, array $params = [])
    {
        $this->controller = $controller;
        $this->params = $params;
    }

    public function controller()
    {
        return $this->controller;
    }

    public function params()
    {
        return $this->params;
    }
}
