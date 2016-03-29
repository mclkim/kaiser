<?php

namespace Kaiser;

$loader = require_once BASE_PATH . '/vendor/mclkim/kaiser/vendor/autoload.php';
// use Aura\Web\Request;
use Aura\Web\WebFactory;

class Test extends Singleton {
	function test() {
		// $request = new \Aura\Web\Request ();
		$web_factory = new WebFactory ( array (
				'_ENV' => $_ENV,
				'_GET' => $_GET,
				'_POST' => $_POST,
				'_COOKIE' => $_COOKIE,
				'_SERVER' => $_SERVER 
		) );
		
		echo 'hello';
	}
}