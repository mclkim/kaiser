<?php

namespace Kaiser;

// $loader = require_once BASE_PATH . '/vendor/mclkim/kaiser/vendor/autoload.php';
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
		
		// echo 'hello';
		$request = $web_factory->newRequest ();
		$response = $web_factory->newResponse ();
		
		var_dump ( $request->cookies );
		var_dump ( $request->env );
		var_dump ( $request->files );
		var_dump ( $request->post );
		var_dump ( $request->query );
		var_dump ( $request->server );
		var_dump ( $request->client );
		var_dump ( $request->content );
		var_dump ( $request->headers );
		var_dump ( $request->method );
		var_dump ( $request->params );
		var_dump ( $request->url );
		// var_dump ( $response );
	}
}