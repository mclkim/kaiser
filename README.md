# The Kaiser framework for PHP

## Description

Kaiser is a PHP simple framework that helps you quickly write simple web applications and APIs.

```php
<?php
use Mcl\Kaiser\Middleware\AuthMiddleware;
use Mcl\Kaiser\Middleware\RoutingMiddleware;
use Mcl\Kaiser\Middleware\SessionMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");
    return $response;
});

//TODO::mclkim
$route = new RoutingMiddleware($app->getContainer(), __DIR__ . '/../app');
if ($route = $route()) {
    $app->map($route['methods'], $route['pattern'], $route['handler'])
        ->add(new SessionMiddleware())
        ->add(new AuthMiddleware($route['callable']));
}

$app->run();
```

## Prerequisites

[PHP](http://php.net/)
```
$ php -v
PHP 7.3.8 (cli) (built: Jul 30 2019 12:44:06) ( ZTS MSVC15 (Visual C++ 2017) x64
 )
Copyright (c) 1997-2018 The PHP Group
Zend Engine v3.3.8, Copyright (c) 1998-2018 Zend Technologies
    with Zend OPcache v7.3.8, Copyright (c) 1999-2018, by Zend Technologies
```
[Composer](https://getcomposer.org/)
```
$ composer -v
   ______
  / ____/___  ____ ___  ____  ____  ________  _____
 / /   / __ \/ __ `__ \/ __ \/ __ \/ ___/ _ \/ ___/
/ /___/ /_/ / / / / / / /_/ / /_/ (__  )  __/ /
\____/\____/_/ /_/ /_/ .___/\____/____/\___/_/
                    /_/
Composer version 1.9.1 2019-11-01 17:20:17
```

## 1.Install
First, at the command line, make working directory:
```
$ mkdir homepage
$ cd homepage
```
and require the necessary libraries:
```
$ composer require mclkim/kaiser
```

## 2.Example copy on local development
The following is a working example. 
```
$ cp -rf vendor/mclkim/kaiser/example/* .
```

## 3.Web brower
You can test the framework using the [public/index.php](public/index.php)
example. You can run the demo using the internal web server of PHP with the
following command:
```
$ php -S localhost:8000 -t public public/index.php
```
... and point your browser to http://localhost:8000/ 

```
http://localhost:8000/
http://localhost:8000/hello/world
http://localhost:8000/hello.world?p1=1&p2=2&p3=3
```
## Reference
 * [Slim Framework](https://github.com/slimphp)
 * [KLogger: Simple Logging for PHP](https://github.com/katzgrau/KLogger) 
 * [Template_](http://www.xtac.net)
 * [PHP-DB](https://github.com/delight-im/PHP-DB)

Released under the [MIT License](LICENSE)
