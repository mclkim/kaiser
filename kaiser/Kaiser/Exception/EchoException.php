<?php

namespace Kaiser\Exception;

class EchoException extends \ErrorException {
	var $charset = 'utf-8';
	function __construct($message = false, $code = false) {
		parent::__construct ( $message, $code );
	}
	function displayError() {
		echo json_encode ( array (
				'code' => $this->code,
				'message' => $this->message 
		) );
	}
}
?>
