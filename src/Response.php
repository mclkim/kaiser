<?php

namespace Kaiser;

class Response extends Singleton {
	protected $http;
	protected $original;
	function __construct() {
		$this->http = require BASE_PATH . '/vendor/mclkim/kaiser/vendor/aura/http/scripts/instance.php';
	}
	protected function morphToJson($content) {
		if ($content instanceof Jsonable)
			return $content->toJson ();
		
		return json_encode ( $content );
	}
	protected function shouldBeJson($content) {
		return $content instanceof Jsonable || $content instanceof ArrayObject || is_array ( $content );
	}
	function setContent($content) {
		$this->original = $content;
		
		$response = $this->http->newResponse ();
		
		// If the content is "JSONable" we will set the appropriate header and convert
		// the content to JSON. This is useful when returning something like models
		// from routes that will be automatically transformed to their JSON form.
		if ($this->shouldBeJson ( $content )) {
			$response->headers->set ( 'Content-Type', 'application/json' );
			
			$content = $this->morphToJson ( $content );
		}		

		// If this content implements the "Renderable" interface then we will call the
		// render method on the object so we will avoid any "__toString" exceptions
		// that might be thrown and have their errors obscured by PHP's handling.
		elseif ($content instanceof Renderable) {
			$content = $content->render ();
		}
		
		$response->setContent ( $content );
		
		return $this->http->send ( $response );
	}
}
