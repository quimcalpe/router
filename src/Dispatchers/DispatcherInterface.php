<?php
namespace QuimCalpe\Router\Dispatchers;

use QuimCalpe\Router\Route\ParsedRoute;

interface DispatcherInterface
{
    public function handle(ParsedRoute $route);
}
