<?php
use \Kaiser\Controller;
class index extends Controller {
	protected function requireLogin() {
		return false;
	}
	function execute() {
		echo 'hello world';
	}
}