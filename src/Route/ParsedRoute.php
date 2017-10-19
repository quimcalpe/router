<?php
namespace QuimCalpe\Router\Route;

class ParsedRoute
{
    private $controller;
    private $params;

    public function __construct(string $controller, array $params = [])
    {
        $this->controller = $controller;
        $this->params = $params;
    }

    public function controller(): string
    {
        return $this->controller;
    }

    public function params(): array
    {
        return $this->params;
    }
}
