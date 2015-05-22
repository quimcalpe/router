<?php
namespace QuimCalpe\Router\Router\Test;

use QuimCalpe\Router\Router;
use PHPUnit_Framework_TestCase as TestCase;

class RouterTest extends TestCase
{
    private $routes = [
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
        $this->assertEquals("Vendor\Package\Controller", $router->parse("GET", "/segment")->controller());
        $this->assertEquals("Vendor\Package1\Controller2", $router->parse("GET", "/segment1/segment2")->controller());
        $this->assertEquals("Vendor\Package1\Controller2", $router->parse("GET", "/segment1/segment2?q=123&key=adsds")->controller());
        $this->assertEquals("Vendor\Package1\Controller4", $router->parse("GET", "/segment1/segment4")->controller());
        $this->assertEquals("Vendor\Package1\Controller4::edit", $router->parse("POST", "/segment1/segment4")->controller());
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
        $resultado = $router->parse("GET", "/segment5/some_controller");
        $this->assertEquals("Vendor\Package5\{controller}", $resultado->controller());
        $this->assertEquals("some_controller", $resultado->params()["controller"]);

        $resultado = $router->parse("GET", "/segment5/some_controller/some_action");
        $this->assertEquals("Vendor\Package5\{controller}::{action}", $resultado->controller());
        $this->assertEquals("some_controller", $resultado->params()["controller"]);
        $this->assertEquals("some_action", $resultado->params()["action"]);

        $resultado = $router->parse("GET", "/some_package/some_controller");
        $this->assertEquals("Vendor\{package}\{controller}", $resultado->controller());
        $this->assertEquals("some_package", $resultado->params()["package"]);
        $this->assertEquals("some_controller", $resultado->params()["controller"]);
    }

    public function testNotFound()
    {
        $this->setExpectedException('QuimCalpe\Router\RouteNotFoundException');
        $router = new Router($this->routes);
        $router->parse("GET", "/bad/route/whatever");
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

    public function testAddRouteMethodNotAllowed()
    {
        $this->setExpectedException('QuimCalpe\Router\MethodNotAllowedException');
        $router = new Router;
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

        $resultado = $router->parse("GET", "/customer/123");
        $this->assertEquals("Vendor\Package\Controller", $resultado->controller());
        $this->assertEquals(123, $resultado->params()["id"]);

        $resultado = $router->parse("GET", "/customer/John");
        $this->assertEquals("Vendor\Package\Controller", $resultado->controller());
        $this->assertEquals("John", $resultado->params()["name"]);

        $resultado = $router->parse("GET", "/customer/1-222-333-4444");
        $this->assertEquals("Vendor\Package\Controller", $resultado->controller());
        $this->assertEquals("1-222-333-4444", $resultado->params()["phone"]);
    }

}
