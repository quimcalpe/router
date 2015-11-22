<?php
namespace QuimCalpe\Router\Router\Test;

use QuimCalpe\Router\Router;
use PHPUnit_Framework_TestCase as TestCase;

class RouterTest extends TestCase
{
    private $routes = [
        "/" => "Vendor\Package\HomeController",
        "/segment" => "Vendor\Package\Controller",
        "/segment1/segment2" => "Vendor\Package1\Controller2",
        "/segment1/segment4" => "Vendor\Package1\Controller4",
        "[POST]/segment1/segment4" => "Vendor\Package1\Controller4::edit",
        "[GET|POST]/segment5/{controller}" => "Vendor\Package5\{controller}",
        "/segment5/{controller}/{action}" => "Vendor\Package5\{controller}::{action}",
        "/{package}/{controller}" => "Vendor\{package}\{controller}"
    ];

    public function testFound()
    {
        $router = new Router($this->routes);
        $this->assertEquals("Vendor\Package\HomeController", $router->parse("GET", "/")->controller());
        $this->assertEquals("Vendor\Package\Controller", $router->parse("GET", "/segment")->controller());
        $this->assertEquals("Vendor\Package1\Controller2", $router->parse("GET", "/segment1/segment2")->controller());
        $this->assertEquals("Vendor\Package1\Controller2", $router->parse("GET", "/segment1/segment2?q=123&key=adsds")->controller());
        $this->assertEquals("Vendor\Package1\Controller4", $router->parse("GET", "/segment1/segment4")->controller());
        $this->assertEquals("Vendor\Package1\Controller4::edit", $router->parse("POST", "/segment1/segment4")->controller());
    }

    public function testFoundWithPrefix()
    {
        $router = new Router($this->routes);
        $this->assertEquals("Vendor\Package\HomeController", $router->parse("GET", "/segment0/", "segment0")->controller());
        $this->assertEquals("Vendor\Package1\Controller2", $router->parse("GET", "/segment0/segment1/segment2", "segment0")->controller());
        $this->assertEquals("Vendor\Package1\Controller2", $router->parse("GET", "/segment0/segment1/segment2?q=123&key=adsds", "segment0")->controller());
        $this->assertEquals("Vendor\Package1\Controller4", $router->parse("GET", "/segment0/segment1/segment4", "segment0")->controller());
        $this->assertEquals("Vendor\Package1\Controller4::edit", $router->parse("POST", "/segment0/segment1/segment4", "segment0")->controller());
        $this->assertEquals("Vendor\Package1\Controller4::edit", $router->parse("POST", "/prefix/segment1/segment4", "prefix")->controller());
        $this->assertEquals("Vendor\Package1\Controller4::edit", $router->parse("POST", "/prefix/segment1/segment4", "/prefix")->controller());
    }

    public function testTrailingSlashNotFound()
    {
        $this->setExpectedException('QuimCalpe\Router\RouteNotFoundException');
        $router = new Router($this->routes);
        $router->parse("GET", "/segment1/segment2/");
    }

    public function testTrailingSlashCheckDisabled()
    {
        $router = new Router($this->routes);
        $router->disableTrailingSlashCheck();
        $this->assertEquals("Vendor\Package1\Controller2", $router->parse("GET", "/segment1/segment2")->controller());
        $this->assertEquals("Vendor\Package1\Controller2", $router->parse("GET", "/segment1/segment2/")->controller());
        $this->assertEquals("Vendor\Package1\Controller2", $router->parse("GET", "/segment1/segment2?q=123&key=adsds")->controller());
        $this->assertEquals("Vendor\Package1\Controller2", $router->parse("GET", "/segment1/segment2/?q=123&key=adsds")->controller());
    }

    public function testFoundWithParams()
    {
        $router = new Router($this->routes);
        $result = $router->parse("GET", "/segment5/some_controller");
        $this->assertEquals("Vendor\Package5\{controller}", $result->controller());
        $this->assertEquals("some_controller", $result->params()["controller"]);

        $result = $router->parse("GET", "/segment5/some_controller/some_action");
        $this->assertEquals("Vendor\Package5\{controller}::{action}", $result->controller());
        $this->assertEquals("some_controller", $result->params()["controller"]);
        $this->assertEquals("some_action", $result->params()["action"]);

        $result = $router->parse("GET", "/some_package/some_controller");
        $this->assertEquals("Vendor\{package}\{controller}", $result->controller());
        $this->assertEquals("some_package", $result->params()["package"]);
        $this->assertEquals("some_controller", $result->params()["controller"]);
    }

    public function testNotFound()
    {
        $this->setExpectedException('QuimCalpe\Router\RouteNotFoundException');
        $router = new Router($this->routes);
        $router->parse("GET", "/bad/route/whatever");
    }

    public function testNotFound2()
    {
        $this->setExpectedException('QuimCalpe\Router\RouteNotFoundException');
        $router = new Router($this->routes);
        $router->parse("PUT", "/bad/route/whatever");
    }

    public function testAddRoute()
    {
        $router = new Router([
            "/segment1/segment4" => "Vendor\Package1\Controller4"
        ]);
        $router->addRoute("GET", "/segment", "Vendor\Package\Controller");
        $router->addRoute("POST", "/segment1/segment2", "Vendor\Package1\Controller2");
        $router->addRoute(["POST", "PUT"], "/segment2", "Vendor\Package\Controller2");

        $this->assertEquals("Vendor\Package\Controller", $router->parse("GET", "/segment")->controller());
        $this->assertEquals("Vendor\Package1\Controller2", $router->parse("POST", "/segment1/segment2")->controller());
        $this->assertEquals("Vendor\Package1\Controller4", $router->parse("GET", "/segment1/segment4")->controller());
        $this->assertEquals("Vendor\Package\Controller2", $router->parse("PUT", "/segment2")->controller());
        $this->assertEquals("Vendor\Package\Controller2", $router->parse("POST", "/segment2")->controller());
    }

    public function testAddHead()
    {
        $router = new Router();
        $router->addHead("/segment", "Vendor\Package\Controller");
        $this->assertEquals("Vendor\Package\Controller", $router->parse("HEAD", "/segment")->controller());
    }
    public function testAddGet()
    {
        $router = new Router();
        $router->addGet("/segment", "Vendor\Package\Controller");
        $this->assertEquals("Vendor\Package\Controller", $router->parse("GET", "/segment")->controller());
    }
    public function testAddDelete()
    {
        $router = new Router();
        $router->addDelete("/segment", "Vendor\Package\Controller");
        $this->assertEquals("Vendor\Package\Controller", $router->parse("DELETE", "/segment")->controller());
    }
    public function testAddOptions()
    {
        $router = new Router();
        $router->addOptions("/segment", "Vendor\Package\Controller");
        $this->assertEquals("Vendor\Package\Controller", $router->parse("OPTIONS", "/segment")->controller());
    }
    public function testAddPatch()
    {
        $router = new Router();
        $router->addPatch("/segment", "Vendor\Package\Controller");
        $this->assertEquals("Vendor\Package\Controller", $router->parse("PATCH", "/segment")->controller());
    }
    public function testAddPost()
    {
        $router = new Router();
        $router->addPost("/segment", "Vendor\Package\Controller");
        $this->assertEquals("Vendor\Package\Controller", $router->parse("POST", "/segment")->controller());
    }
    public function testAddPut()
    {
        $router = new Router();
        $router->addPut("/segment", "Vendor\Package\Controller");
        $this->assertEquals("Vendor\Package\Controller", $router->parse("PUT", "/segment")->controller());
    }

    public function testAddRouteMethodNotAllowed()
    {
        $this->setExpectedException('QuimCalpe\Router\MethodNotAllowedException');
        $router = new Router;
        $router->addRoute("GET", "/segment", "Vendor\Package\Controller");
        $router->addRoute(["POST", "PUT"], "/segment2", "Vendor\Package\Controller2");
        $router->parse("GET", "/segment2");
    }

    public function testPattern()
    {
        $router = new Router();

        $router->addRoute("GET", "/customer/{id:number}", "Vendor\Package\Controller");
        $router->addRoute("GET", "/customer/{name:word}", "Vendor\Package\Controller");
        $router->addRoute("GET", "/customer/{phone:phone}", "Vendor\Package\Controller");

        $router->addPattern("phone", "[0-9]-[0-9]{3}-[[0-9]{3}-[0-9]{4}"); // #-###-###-####

        $result = $router->parse("GET", "/customer/123");
        $this->assertEquals("Vendor\Package\Controller", $result->controller());
        $this->assertEquals(123, $result->params()["id"]);

        $result = $router->parse("GET", "/customer/John");
        $this->assertEquals("Vendor\Package\Controller", $result->controller());
        $this->assertEquals("John", $result->params()["name"]);

        $result = $router->parse("GET", "/customer/1-222-333-4444");
        $this->assertEquals("Vendor\Package\Controller", $result->controller());
        $this->assertEquals("1-222-333-4444", $result->params()["phone"]);
    }

    public function test_findURI()
    {
        $router = new Router();

        $router->addRoute("GET", "/customer/route1", "Vendor\Package\Controller1", "route1");
        $router->addRoute("GET", "/customer/route2", "Vendor\Package\Controller2");
        $this->assertEquals("/customer/route1", $router->findURI("route1"));
        $this->assertNull($router->findURI("routefoo"));
    }

    public function test_findURI_with_parameters()
    {
        $router = new Router();

        $router->addRoute("GET", "/customer/{id:number}", "Vendor\Package\Controller1", "route1");
        $this->assertEquals("/customer/123", $router->findURI("route1", ["id" => 123]));

        $router->addRoute("GET", "/customer/{id}/{action}", "Vendor\Package\Controller2", "route2");
        $this->assertEquals("/customer/123/edit", $router->findURI("route2", [
            "id" => 123,
            "action" => "edit"
        ]));

        $router->addRoute("GET", "/customer/{id}/{action}", "Vendor\Package\Controller2", "route3");
        $this->assertEquals("/customer/{id}/{action}", $router->findURI("route3"));
    }
}
