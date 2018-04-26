kaiser
=============
Kaiser PHP framework

Description
-------------

Prerequisites
-------------
###### [PHP]http://php.net/
```
$ php -v
PHP 5.5.X (cli) (built: Jul  6 2017 16:51:52) ( ZTS MSVC14 (Visual C++ 2015) x64 )
Copyright (c) 1997-2017 The PHP Group
Zend Engine v3.1.0, Copyright (c) 1998-2017 Zend Technologies
    with Zend OPcache v7.1.7, Copyright (c) 1999-2017, by Zend Technologies
```
###### [Composer]https://getcomposer.org/
```
$ composer --version
Composer version 1.5.1 2017-08-09 16:07:22
```

###1.Install
```
$ mkdir homepage
$ cd homepage
$ composer require mclkim/kaiser:dev-master
./composer.json has been created
Loading composer repositories with package information
Updating dependencies (including require-dev)
Package operations: 6 installs, 0 updates, 0 removals
  - Installing psr/log (1.0.2): Loading from cache
  - Installing katzgrau/klogger (1.2.1): Loading from cache
  - Installing aura/web (2.1.0): Loading from cache
  - Installing psr/container (1.0.0): Loading from cache
  - Installing pimple/pimple (v3.2.3): Loading from cache
  - Installing mclkim/kaiser (dev-master 89be385): Cloning 89be385ac9 from cache
Writing lock file
Generating autoload files
```

###2.Example copy on local development
```
$ cp -rf vendor/mclkim/kaiser/example/* .
$ php -S localhost:8000 -t public/
PHP 5.5.X Development Server started at Thu Apr 26 14:56:29 2018
Listening on http://localhost:8000
Document root is /workspace/homepage/public
Press Ctrl-C to quit.
```

###3.Web brower
```
http://localhost:8000/?
http://localhost:8000/?mysql
http://localhost:8000/?hello.world&p1=1&p2=2&p3=3
```
