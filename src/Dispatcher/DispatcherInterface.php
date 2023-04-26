<?php
namespace QuimCalpe\Router\Dispatcher;

use QuimCalpe\Router\Route\ParsedRoute;

interface DispatcherInterface
{
    public function handle(ParsedRoute $route): mixed;
}
