<?php
namespace QuimCalpe\Router\Test;

use QuimCalpe\Router\Route\ParsedRoute;
use QuimCalpe\Router\Dispatcher\PSR7Dispatcher;
use Vendor\Package\MockControllerPSR7 as MockController;
use PHPUnit_Framework_TestCase as TestCase;

class PSR7DispatcherTest extends TestCase
{
    public function testIndexAction()
    {
        $dispatcher = new PSR7Dispatcher(new MockPSR7ServerRequest(), new MockPSR7Response());
        $parsedRoute = new ParsedRoute("Vendor\Package\MockControllerPSR7::index");
        $controller = new MockController;
        $dispatcher->handle($parsedRoute);
        $this->assertTrue($controller::$index);

        $dispatcher = new PSR7Dispatcher(new MockPSR7ServerRequest(), new MockPSR7Response());
        $parsedRoute = new ParsedRoute("Vendor\Package\MockControllerPSR7");
        $controller = new MockController;
        $dispatcher->handle($parsedRoute);
        $this->assertTrue($controller::$index);

        $dispatcher = new PSR7Dispatcher(new MockPSR7ServerRequest(), new MockPSR7Response());
        $parsedRoute = new ParsedRoute("Vendor\Package\MockControllerPSR7::edit");
        $controller = new MockController;
        $dispatcher->handle($parsedRoute);
        $this->assertFalse($controller::$index);
    }

    public function testEditAction()
    {
        $dispatcher = new PSR7Dispatcher(new MockPSR7ServerRequest(), new MockPSR7Response());
        $parsedRoute = new ParsedRoute("Vendor\Package\MockControllerPSR7::edit", [
            "param1" => 1,
            "param2" => "two"
        ]);
        $controller = new MockController;
        $dispatcher->handle($parsedRoute);
        $this->assertTrue(is_array($controller::$edit));
        $this->assertEquals(1, $controller::$edit["param1"]);
        $this->assertEquals("two", $controller::$edit["param2"]);
    }

    public function test_correct_response()
    {
        $_GET["arg1"] = "hola";
        $dispatcher = new PSR7Dispatcher(new MockPSR7ServerRequest($_GET), new MockPSR7Response());
        $parsedRoute = new ParsedRoute("Vendor\Package\MockControllerPSR7::index2");
        $controller = new MockController;
        $response = $dispatcher->handle($parsedRoute);
        $this->assertEquals("response: hola", $response->getBody());
        unset($_GET["arg1"]);
    }

    public function testBadAction()
    {
        $this->setExpectedException('RunTimeException');
        $dispatcher = new PSR7Dispatcher(new MockPSR7ServerRequest(), new MockPSR7Response());
        $parsedRoute = new ParsedRoute("Vendor\Package\MockControllerPSR7::nono");
        $dispatcher->handle($parsedRoute);
    }
}

namespace Vendor\Package;

class MockControllerPSR7
{
    public static $index;
    public static $edit;

    public function __construct()
    {
        self::$index = false;
        self::$edit = null;
    }

    public function index($request, $response)
    {
        self::$index = true;
        return $response;
    }

    public function index2($request, $response)
    {
        self::$index = true;
        $response = new \QuimCalpe\Router\Test\MockPSR7Response("response: ".$request->getQueryParams()["arg1"]);
        return $response;
    }

    public function edit($request, $response, array $params = null)
    {
        self::$edit = $params;
    }
}
