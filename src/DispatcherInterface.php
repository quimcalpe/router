<?php
namespace QuimCalpe\Router;

interface DispatcherInterface
{
    public function handle(ParsedRoute $route);
}
