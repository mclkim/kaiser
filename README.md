# kaiser
Kaiser PHP framework

```
composer require mclkim/kaiser=dev-master
```

<Application Folder>/public/index.php
``` public/index.php
<?php
require __DIR__ . '/../vendor/autoload.php';

$app = new Kaiser\App ();

$app->setAppDir ( [ 
		__DIR__ . '/../app' 
] );

$app->run ();
```


<Application Folder>/public/app/index.php
``` public/app/index.php
<?php
use \Kaiser\Controller;
/**
 * http://localhost/test/public/?index
 */
class index extends Controller {
	protected function requireLogin() {
		return false;
	}
	function execute() {
		echo 'Kaiser PHP framework~~~';
	}
}
```

<Application Folder>/public/app/hello.php
``` public/app/hello.php
<?php
use \Kaiser\Controller;
/**
 * http://localhost/test/public/?hello.world
 */
class hello extends Controller {
	protected function requireLogin() {
		return false;
	}
	function world() {
		echo 'hello world~~~';
	}
}
```
