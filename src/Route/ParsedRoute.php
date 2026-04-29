<?php
namespace QuimCalpe\Router\Route;

class ParsedRoute
{
    public function __construct(
        private readonly string $controller,
        private readonly array $params = [],
    ) {}

    public function controller(): string
    {
        return $this->controller;
    }

    public function params(): array
    {
        return $this->params;
    }
}
