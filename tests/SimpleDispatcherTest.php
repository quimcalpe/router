<?php
namespace QuimCalpe\Router\Test;

use QuimCalpe\Router\Route\ParsedRoute;
use QuimCalpe\Router\Dispatcher\SimpleDispatcher;
use Vendor\Package\MockControllerSimple as MockController;
use PHPUnit\Framework\TestCase;

class SimpleDispatcherTest extends TestCase
{
    public function testIndexAction()
    {
        $dispatcher = new SimpleDispatcher;
        $parsedRoute = new ParsedRoute("Vendor\Package\MockControllerSimple::index");
        $controller = new MockController;
        $dispatcher->handle($parsedRoute);
        $this->assertTrue($controller::$index);

        $dispatcher = new SimpleDispatcher;
        $parsedRoute = new ParsedRoute("Vendor\Package\MockControllerSimple");
        $controller = new MockController;
        $dispatcher->handle($parsedRoute);
        $this->assertTrue($controller::$index);

        $dispatcher = new SimpleDispatcher;
        $parsedRoute = new ParsedRoute("Vendor\Package\MockControllerSimple::edit");
        $controller = new MockController;
        $dispatcher->handle($parsedRoute);
        $this->assertFalse($controller::$index);
    }

    public function testEditAction()
    {
        $dispatcher = new SimpleDispatcher;
        $parsedRoute = new ParsedRoute("Vendor\Package\MockControllerSimple::edit", [
            "param1" => 1,
            "param2" => "two"
        ]);
        $controller = new MockController;
        $dispatcher->handle($parsedRoute);
        $this->assertTrue(is_array($controller::$edit));
        $this->assertEquals(1, $controller::$edit["param1"]);
        $this->assertEquals("two", $controller::$edit["param2"]);
    }

    public function testResponse()
    {
        $dispatcher = new SimpleDispatcher;
        $parsedRoute = new ParsedRoute("Vendor\Package\MockControllerSimple::index");
        $controller = new MockController;
        $response = $dispatcher->handle($parsedRoute);
        $this->assertEquals("response", $response);
    }

    public function testBadAction()
    {
        $this->expectException('RunTimeException');
        $dispatcher = new SimpleDispatcher;
        $parsedRoute = new ParsedRoute("Vendor\Package\MockControllerSimple::nono");
        $dispatcher->handle($parsedRoute);
    }
}

namespace Vendor\Package;

class MockControllerSimple
{
    public static $index;
    public static $edit;

    public function __construct()
    {
        self::$index = false;
        self::$edit = null;
    }

    public function index()
    {
        self::$index = true;
        return "response";
    }

    public function edit(array $params = null)
    {
        self::$edit = $params;
    }
}
