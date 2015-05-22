<?php
namespace QuimCalpe\Router\Router\Test;

use QuimCalpe\Router\ParsedRoute;
use QuimCalpe\Router\RequestResponseDispatcher;
use Vendor\Package\MockControllerRequestResponse AS MockController;
use PHPUnit_Framework_TestCase as TestCase;

class RequestResponseDispatcherTest extends TestCase
{
    public function testIndexAction()
    {
        $dispatcher = new RequestResponseDispatcher;
        $parsedRoute = new ParsedRoute("Vendor\Package\MockControllerRequestResponse::index");
        $controller = new MockController;
        $dispatcher->handle($parsedRoute);
        $this->assertTrue($controller::$index);

        $dispatcher = new RequestResponseDispatcher;
        $parsedRoute = new ParsedRoute("Vendor\Package\MockControllerRequestResponse");
        $controller = new MockController;
        $dispatcher->handle($parsedRoute);
        $this->assertTrue($controller::$index);

        $dispatcher = new RequestResponseDispatcher;
        $parsedRoute = new ParsedRoute("Vendor\Package\MockControllerRequestResponse::edit");
        $controller = new MockController;
        $dispatcher->handle($parsedRoute);
        $this->assertFalse($controller::$index);
    }

    public function testEditAction()
    {
        $dispatcher = new RequestResponseDispatcher;
        $parsedRoute = new ParsedRoute("Vendor\Package\MockControllerRequestResponse::edit", [
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
        $dispatcher = new RequestResponseDispatcher;
        $parsedRoute = new ParsedRoute("Vendor\Package\MockControllerRequestResponse::index");
        $controller = new MockController;
        $_GET["arg1"] = "hola";
        $response = $dispatcher->handle($parsedRoute);
        $this->assertEquals("response: hola", $response->getContent());
    }

    public function testBadAction()
    {
        $this->setExpectedException('RunTimeException');
        $dispatcher = new RequestResponseDispatcher;
        $parsedRoute = new ParsedRoute("Vendor\Package\MockControllerRequestResponse::nono");
        $dispatcher->handle($parsedRoute);
    }

}

namespace Vendor\Package;
class MockControllerRequestResponse
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
        $response->setContent("response: ".$request->get("arg1"));
        return $response;
    }

    public function edit($request, $response, array $params = null)
    {
        self::$edit = $params;
    }
}
