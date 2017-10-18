<?php
namespace QuimCalpe\Router\Test;

use QuimCalpe\Router\Route\ParsedRoute;
use QuimCalpe\Router\Dispatcher\RequestResponseDispatcher;
use Vendor\Package\MockControllerRequestResponse as MockController;
use PHPUnit\Framework\TestCase as TestCase;
use Symfony\Component\HttpFoundation\Request;

class RequestResponseDispatcherTest extends TestCase
{
    public function testIndexAction()
    {
        $dispatcher = new RequestResponseDispatcher(new Request());
        $parsedRoute = new ParsedRoute("Vendor\Package\MockControllerRequestResponse::index");
        $controller = new MockController;
        $dispatcher->handle($parsedRoute);
        $this->assertTrue($controller::$index);

        $dispatcher = new RequestResponseDispatcher(new Request());
        $parsedRoute = new ParsedRoute("Vendor\Package\MockControllerRequestResponse");
        $controller = new MockController;
        $dispatcher->handle($parsedRoute);
        $this->assertTrue($controller::$index);

        $dispatcher = new RequestResponseDispatcher(new Request());
        $parsedRoute = new ParsedRoute("Vendor\Package\MockControllerRequestResponse::edit");
        $controller = new MockController;
        $dispatcher->handle($parsedRoute);
        $this->assertFalse($controller::$index);
    }

    public function testEditAction()
    {
        $dispatcher = new RequestResponseDispatcher(new Request());
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

    public function test_correct_response()
    {
        $_GET["arg1"] = "hola";
        $dispatcher = new RequestResponseDispatcher(Request::createFromGlobals());
        $parsedRoute = new ParsedRoute("Vendor\Package\MockControllerRequestResponse::index");
        $controller = new MockController;
        $response = $dispatcher->handle($parsedRoute);
        $this->assertEquals("response: hola", $response->getContent());
        unset($_GET["arg1"]);
    }

    public function test_mofify_request()
    {
        $parsedRoute = new ParsedRoute("Vendor\Package\MockControllerRequestResponse::index");
        $controller = new MockController;
        $request = new Request();
        $dispatcher = new RequestResponseDispatcher($request);
        $request->attributes->set('arg1', 'bye!');
        $response = $dispatcher->handle($parsedRoute);
        $this->assertEquals("response: bye!", $response->getContent());
    }

    public function testBadAction()
    {
        $this->expectException('RunTimeException');
        $dispatcher = new RequestResponseDispatcher(new Request());
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
