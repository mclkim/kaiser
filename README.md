# The Kaiser framework for PHP

## Description

Kaiser is a PHP simple-framework that helps you quickly write simple web applications and APIs.

## Prerequisites

[PHP](http://php.net/)
```
$ php -v
PHP 5.5.X (cli) (built: Jul  6 2017 16:51:52) ( ZTS MSVC14 (Visual C++ 2015) x64 )
Copyright (c) 1997-2017 The PHP Group
Zend Engine v3.1.0, Copyright (c) 1998-2017 Zend Technologies
    with Zend OPcache v7.1.7, Copyright (c) 1999-2017, by Zend Technologies
```
[Composer](https://getcomposer.org/)
```
$ composer --version
Composer version 1.5.1 2017-08-09 16:07:22
```

## 1.Install

First, at the command line, make working directory:
```
$ mkdir homepage
$ cd homepage
```
and require the necessary libraries:
```
$ composer require mclkim/kaiser:dev-master
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
http://localhost:8000/mysql
http://localhost:8000/hello.world?p1=1&p2=2&p3=3
```
## Reference
<<<<<<< HEAD
 * [Container](https://github.com/silexphp/Pimple)
 * [Logger](https://github.com/katzgrau/KLogger) 
 * [Request and Response](https://github.com/auraphp/Aura.Web)
 * [Router](https://github.com/nikic/FastRoute)
 * [Template_](http://www.xtac.net)
 * [PHP-DB](https://github.com/delight-im/PHP-DB)
=======
>>>>>>> 26aa3e402fdbd25eed47c460755f00c907c00a92

 * [Slim - PHP micro framework](https://www.slimframework.com/)
 * [KLogger: Simple Logging for PHP](https://github.com/katzgrau/klogger)
 
Released under the [MIT License](LICENSE)
