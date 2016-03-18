<?php

namespace Kaiser\Manager;

use Kaiser\Controller;

class AuthManager extends Controller {
	protected $user;
	public function check() {
		if (! $this->requireLogin ()) {
			return true;
		}
		if (is_null ( $this->user )) {
			/**
			 * Check supplied session/cookie is an array (username, persist code)
			 */
			if (! ($user = $this->getUser ())) {
				return false;
			}
			/**
			 * Pass
			 */
			$this->user = $user;
		}
		return true;
	}
	public function setUser($user) {
		$_SESSION ['user'] = $user;
	}
	public function getUser() {
		return if_exists ( $_SESSION, 'user', false );
	}
}