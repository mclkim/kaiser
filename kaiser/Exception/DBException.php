<?php

namespace Kaiser\Exception;

class DBException extends DefaultException {
	var $charset = 'euc-kr';
	function __construct($message = false, $code = false) {
		parent::__construct ( $message, $code );
	}
}
?>
