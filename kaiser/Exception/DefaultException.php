<?php

namespace Kaiser\Exception;

class DefaultException extends \ErrorException {
	var $heading = 'Error';
	var $class = null;
	var $backtrace = null;
	var $debug = false;
	var $charset = 'utf-8';
	function __construct($message = false, $code = false) {
		parent::__construct ( $message, $code );
		
		$this->class = get_class ( $this );
		$this->backtrace = debug_backtrace ();
	}
	function displayError() {
		$levels = array (
				E_ERROR => 'Error',
				E_WARNING => 'Warning',
				E_PARSE => 'Parsing Error',
				E_NOTICE => 'Notice',
				E_CORE_ERROR => 'Core Error',
				E_CORE_WARNING => 'Core Warning',
				E_COMPILE_ERROR => 'Compile Error',
				E_COMPILE_WARNING => 'Compile Warning',
				E_USER_ERROR => 'User Error',
				E_USER_WARNING => 'User Warning',
				E_USER_NOTICE => 'User Notice',
				E_STRICT => 'Runtime Notice' 
		);
		$title = $levels [$this->severity];
		
		echo <<<HEAD
<html>
<head>
<title>$this->heading</title>
<meta charset="$this->charset" />
<style type="text/css">

body {
	background-color: #fff;
	margin: 40px;
	font: 13px/20px normal Helvetica, Arial, sans-serif;
	color: #4F5155;
}

a {
	color: #003399;
	background-color: transparent;
	font-weight: normal;
}

h1 {
	color: #444;
	background-color: transparent;
	border-bottom: 1px solid #D0D0D0;
	font-size: 19px;
	font-weight: normal;
	margin: 0 0 14px 0;
	padding: 14px 15px 10px 15px;
}

code {
	font-family: Consolas, Monaco, Courier New, Courier, monospace;
	font-size: 12px;
	background-color: #f9f9f9;
	border: 1px solid #D0D0D0;
	color: #002166;
	display: block;
	margin: 14px 0 14px 0;
	padding: 12px 10px 12px 10px;
}

#container {
	margin: 10px;
	border: 1px solid #D0D0D0;
	-webkit-box-shadow: 0 0 8px #D0D0D0;
}

#infldset { 
	padding-left: 20px; 
} 

p {
	margin: 12px 15px 12px 15px;
}
</style>
</head>
<body>
<div id="container">
<h1>$title</h1>
<p>$this->message</p>
<div id="infldset">
HEAD;
		if (isset ( $this->class )) {
			if ($this->debug) {
				foreach ( $this as $_key => $_val ) {
					if ($_key != "backtrace" && $_key != "xdebug_message")
						echo strtoupper ( $_key ) . ": " . ($_val) . "<br/>";
				}
				
				echo "<br />-- Backtrace a file with an array set --";
				echo "<pre>";
				print_r ( $this->backtrace [1] );
				echo "</pre>";
			}
		}
		echo <<<END
</div>
</div>
</body>
</html>
END;
		exit ();
	}
}
