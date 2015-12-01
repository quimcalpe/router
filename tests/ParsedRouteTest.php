<?php
namespace QuimCalpe\Router\Test;

use QuimCalpe\Router\Route\ParsedRoute;
use PHPUnit_Framework_TestCase as TestCase;

class ParsedRouteTest extends TestCase
{
    public function testConstructor()
    {
        $parsedRoute = new ParsedRoute("Vendor\\Package\\{controller}::action", [
            "param1" => 1,
            "param2" => "two"
        ]);

        $this->assertSame("Vendor\\Package\\{controller}::action", $parsedRoute->controller());
        $this->assertSame(1, $parsedRoute->params()["param1"]);
        $this->assertSame("two", $parsedRoute->params()["param2"]);
    }
}
