# quimcalpe/router

[![Version](https://img.shields.io/packagist/v/quimcalpe/router.svg?style=flat-square)](https://packagist.org/packages/quimcalpe/router)
[![License](https://img.shields.io/packagist/l/quimcalpe/router.svg?style=flat-square)](https://packagist.org/packages/quimcalpe/router)
[![Build Status](https://img.shields.io/travis/quimcalpe/router.svg?style=flat-square)](http://travis-ci.org/quimcalpe/router)
[![Code Coverage](https://scrutinizer-ci.com/g/quimcalpe/router/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/quimcalpe/router/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/quimcalpe/router/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/quimcalpe/router/?branch=master)

Regexp based Router, easy to use and with a rich feature set. Various built-in Dispatchers are included, and an interface is also provided to enable developing full customized Dispatchers for your project.

## Install

Via Composer

``` bash
$ composer require quimcalpe/router
```

## Requirements

The following versions of PHP are supported by this version.

* PHP 5.4
* PHP 5.5
* PHP 5.6
* PHP 7
* HHVM


## Basic Usage

```php
// Require composer autoloader
require __DIR__ . '/vendor/autoload.php';

use QuimCalpe\Router\Router;
use QuimCalpe\Router\Dispatcher\SimpleDispatcher;

// Create Router instance
$router = new Router();

// Define routes, last parameter defining route name is optional
$router->addRoute('GET', '/users', 'Quimi\Controllers\UserController', 'user_list');
$router->addRoute('GET', '/users/edit/{id:number}', 'Quimi\Controllers\UserController::edit', 'user_edit');
$router->addRoute(['POST', 'DELETE'], '/users/remove/{id:number}', 'Quimi\Controllers\UserController::remove', 'user_delete');

// Sugar methods for common verbs are also available (GET, POST, PUT, DELETE...)
$router->addGet('/user/{id}', 'Quimi\Controllers\UserController::show', 'user_show');

// You can also create a QuimCalpe\Router\Route\Route value object and add directly to router's `->add()`
$route = new Route('GET', '/', 'Quimi\Controllers\HomeController', 'home');
$router->add($route);

try {
    // Match routes
    $route = $router->parse($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
    // Dispatch route
    $dispatcher = new SimpleDispatcher();
    $response = $dispatcher->handle($route);
} catch (QuimCalpe\Router\Exception\MethodNotAllowedException $e) {
	header('HTTP/1.0 405 Method Not Allowed');
	// exception message contains allowed methods
	header('Allow: '.$e->getMessage());
} catch (QuimCalpe\Router\Exception\RouteNotFoundException $e) {
	header('HTTP/1.0 404 Not Found');
    // not found....
}
```

## Constructor optional Route[] parameter

You can alternatively pass an array of `Route` objects to Router's constructor, and routes will be created;

```php
use QuimCalpe\Router\Router;
use QuimCalpe\Router\Route\Route;

$routes = [
	new Route('GET', '/users', 'Quimi\Controllers\UserController', 'user_list'),
	new Route('GET', '/users/edit/{id:number}', 'Quimi\Controllers\UserController::edit', 'user_edit'),
	new Route(['POST', 'DELETE'], '/users/remove/{id:number}', 'Quimi\Controllers\UserController::remove', 'user_delete'),
]

$router = new Router($routes);
```

This array can be included from another file, enabling config separation in a simple way.

### Route Patterns

Basic regexp patterns are supported, some are already included:

- `[^/]+` as default
- 'word' => `\w+`
- 'number' => `\d+`
- 'slug' => `[A-Za-z0-9_-]+`

Patterns can be used this way:

```php
$router->addRoute('GET', '/users/edit/{id:number}', 'Controller::action');
$router->addRoute('GET', '/users/{name:word}', 'Controller::action');
```

You can define your own patterns:

```php
$router->addPattern("phone", "[0-9]-[0-9]{3}-[[0-9]{3}-[0-9]{4}"); // #-###-###-####
$router->addRoute("GET", "/customer/{phone:phone}", "Vendor\Package\Controller");
$parsedRoute = $router->parse("GET", "/customer/1-222-333-4444");
```

### Wildcards

Wildcards in routes can be used with `WildcardDispatcher`:

```php
$router->addRoute('GET', '/test/{controller}/{action}/{id}', 'Vendor\Package\{controller}::{action}');
$parsedRoute = $router->parse("GET", "/test/user/edit");
$dispatcher = new WildcardDispatcher;
$response = $dispatcher->handle($parsedRoute); // => Vendor\Package\User::edit($id)
```

### Request Response

Standard Request - Response workflow with Symfony HttpFoundation components is supported with `RequestResponseDispatcher`:

```php
use Symfony\Component\HttpFoundation\Request;
use QuimCalpe\Router\Router;
use QuimCalpe\Router\Dispatcher\RequestResponseDispatcher;

$router = new Router();
$router->addRoute('GET', '/users', 'Quimi\Controllers\UserController::index');

$request = Request::createFromGlobals();
$route = $router->parse($request->getMethod(), $request->getPathInfo());

// You can optionally modify the request object here before dispatching:
$request->attributes->set('foo', 'bar');

$dispatcher = new RequestResponseDispatcher($request);
$response = $dispatcher->handle($route);
$response->send();
```

### PSR-7 HTTP Message

A built-in `PSR7Dispatcher` is available to work with PHP-FIG's PSR-7 HTTP Message standard implementations, an example using [Zend Diactoros](https://github.com/zendframework/zend-diactoros) and a simple [PSR-7 Response Sender](https://github.com/quimcalpe/psr7-response-sender) would look like this:

```php
use QuimCalpe\Router\Router;
use QuimCalpe\Router\Route\Route;
use QuimCalpe\Router\Dispatcher\PSR7Dispatcher;
use function QuimCalpe\ResponseSender\send AS send_response;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Response;

$router = new Router();
$router->add(new Route("GET", "/test", "ControllerFoo"));

$request = ServerRequestFactory::fromGlobals();
$route = $router->parse($request->getMethod(), $request->getUri()->getPath());

$dispatcher = new PSR7Dispatcher($request, new Response());
$response = $dispatcher->handle($route);
send_response($response);
```

### Custom Dispatcher

You can create your custom Dispatcher, implementing `DispatcherInterface`:

```php
interface DispatcherInterface
{
    public function handle(ParsedRoute $route);
}
```

`QuimCalpe\Router\Route\ParsedRoute` is a small Value Object with `controller()` and `params()` methods already parsed by `Router::parse`.

### Trailing slash

Default behaviour is to honour distinction between routes with and wothout trailing slashes:

```php
$router = new Router();
$router->addRoute('GET', '/users', 'Controller');
$router->parse('GET', '/users'); // => OK!
$router->parse('GET', '/users/'); // => NOT FOUND
```

You can disable this behaviour with `disableTrailingSlashCheck` method:

```php
$router = new Router();
$router->addRoute('GET', '/users', 'Controller');
$router->disableTrailingSlashCheck();
$router->parse('GET', '/users'); // => OK!
$router->parse('GET', '/users/'); // => OK!
```

## Testing

``` bash
$ vendor/bin/phpunit
```

## License

The MIT License (MIT). Please see [License File](https://github.com/quimcalpe/router/blob/master/LICENSE.md) for more information.
