<?php

namespace Kaiser\Exception;

class PageNotFound extends DefaultException {
	var $heading = "404 Page Not Found";
	var $message = "The page you requested was not found.";
	function __construct($message = false, $code = 404) {
		parent::__construct ( $message, $code );
		$this->class = null;
	}
}
?>
