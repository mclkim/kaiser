<?php

namespace Kaiser\Exception;

class AlertException extends \ErrorException {
	var $charset = 'utf-8';
	var $redirect = '';
	function __construct($message = false, $redirect = false, $code = false) {
		$this->redirect = $redirect;
		parent::__construct ( $message, $code );
	}
	function displayError() {
		if (! $this->redirect) {
			echo <<<END
        <head><meta http-equiv="Content-Type" content="text/html; charset=$this->charset" /></head>
		<script type="text/javascript">
			<!--
				alert("$this->message");
// 				javascript:history.back();
		   //-->
		</script>
END;
			exit ();
		} else {
			echo <<<END
        <head><meta http-equiv="Content-Type" content="text/html; charset=$this->charset" /></head>
		<script type="text/javascript">
				alert("$this->message");
 				location.href = "$this->redirect";
		</script>
END;
			exit ();
		}
	}
}
