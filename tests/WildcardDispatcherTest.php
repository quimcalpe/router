<?php
namespace QuimCalpe\Router\Test;

use QuimCalpe\Router\Route\ParsedRoute;
use QuimCalpe\Router\Dispatcher\WildcardDispatcher;
use Vendor\Package\MockControllerWildcard as MockController;
use PHPUnit\Framework\TestCase;

class WildcardDispatcherTest extends TestCase
{
    public function testIndexAction()
    {
        $dispatcher = new WildcardDispatcher;
        $parsedRoute = new ParsedRoute("Vendor\Package\MockControllerWildcard::index");
        $controller = new MockController;
        $dispatcher->handle($parsedRoute);
        $this->assertTrue($controller::$index);

        $dispatcher = new WildcardDispatcher;
        $parsedRoute = new ParsedRoute("Vendor\Package\MockControllerWildcard");
        $controller = new MockController;
        $dispatcher->handle($parsedRoute);
        $this->assertTrue($controller::$index);

        $dispatcher = new WildcardDispatcher;
        $parsedRoute = new ParsedRoute("Vendor\Package\MockControllerWildcard::edit");
        $controller = new MockController;
        $dispatcher->handle($parsedRoute);
        $this->assertFalse($controller::$index);
    }

    public function testEditAction()
    {
        $dispatcher = new WildcardDispatcher;
        $parsedRoute = new ParsedRoute("Vendor\Package\MockControllerWildcard::edit", [
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
        $dispatcher = new WildcardDispatcher;
        $parsedRoute = new ParsedRoute("Vendor\Package\MockControllerWildcard::index");
        $controller = new MockController;
        $response = $dispatcher->handle($parsedRoute);
        $this->assertEquals("response", $response);
    }

    public function testBadAction()
    {
        $this->expectException('RunTimeException');
        $dispatcher = new WildcardDispatcher;
        $parsedRoute = new ParsedRoute("Vendor\Package\MockControllerWildcard::nono");
        $dispatcher->handle($parsedRoute);
    }

    public function testWildCards()
    {
        $dispatcher = new WildcardDispatcher;
        $parsedRoute = new ParsedRoute("Vendor\{package}\MockControllerWildcard::{action}", [
            "package" => "Package",
            "action" => "index"
        ]);
        $controller = new MockController;
        $dispatcher->handle($parsedRoute);
        $this->assertTrue($controller::$index);

        $dispatcher = new WildcardDispatcher;
        $parsedRoute = new ParsedRoute("Vendor\{package}\MockControllerWildcard::{action}", [
            "package" => "Package",
            "action" => "edit",
            "param1" => 1,
            "param2" => "two"
        ]);
        $controller = new MockController;
        $dispatcher->handle($parsedRoute);
        $this->assertTrue(is_array($controller::$edit));
        $this->assertEquals(1, $controller::$edit["param1"]);
        $this->assertEquals("two", $controller::$edit["param2"]);
        $this->assertFalse(array_key_exists("package", $controller::$edit));
        $this->assertFalse(array_key_exists("action", $controller::$edit));
    }
}

namespace Vendor\Package;

class MockControllerWildcard
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
