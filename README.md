# quimcalpe/router

[![Build Status](https://img.shields.io/travis/quimcalpe/router.svg?style=flat-square)](http://travis-ci.org/quimcalpe/router) 
[![Version](https://img.shields.io/packagist/v/quimcalpe/router.svg?style=flat-square)](https://packagist.org/packages/quimcalpe/router)
[![License](https://img.shields.io/packagist/l/quimcalpe/router.svg?style=flat-square)](https://packagist.org/packages/quimcalpe/router)

Regexp based router and dispatcher with a simple interface.

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
use QuimCalpe\Router\SimpleDispatcher;

// Create Router instance
$router = new Router();

// Define routes
$router->addRoute('GET', '/users', 'Quimi\Controllers\UserController::index');
$router->addRoute('GET', '/users/edit/{id:number}', 'Quimi\Controllers\UserController::edit');
$router->addRoute(['POST', 'DELETE'], '/users/remove/{id:number}', 'Quimi\Controllers\UserController::remove');

try {
    // Match routes
    $route = $router->parse($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
    // Dispatch route
    $dispatcher = new SimpleDispatcher;
    $response = $dispatcher->handle($route);
} catch (QuimCalpe\Router\MethodNotAllowedException $e) {
	header('HTTP/1.0 405 Method Not Allowed');
	// exception message contains allowed methods
	header('Allow: '.$e->getMessage());
} catch (QuimCalpe\Router\RouteNotFoundException $e) {
	header('HTTP/1.0 404 Not Found');
    // not found....
}
```


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
use QuimCalpe\Router\RequestResponseDispatcher;

$router = new Router();
$router->addRoute('GET', '/users', 'Quimi\Controllers\UserController::index');

$request = Request::createFromGlobals();
$route = $router->parse($request->getMethod(), $request->getPathInfo());

$dispatcher = new RequestResponseDispatcher;
$response = $dispatcher->handle($route);
$response->send();
```


### Custom Dispatcher

You can create your custom Dispatcher, implementing `DispatcherInterface`:

```php
interface DispatcherInterface
{
    public function handle(ParsedRoute $route);
}
```

`QuimCalpe\Router\ParsedRoute` is a small Value Object with `controller()` and `params()` methods already parsed by `Router::parse`.

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
