<?php

namespace Kaiser;

class Controller extends Singleton {
	protected $user;
	protected $container;
	var $_defaultPage = '?';
	var $_loginPage = '?login';
	var $_loginAdminPage = '?loginAdmin';
	function __construct($container = []) {
		if (is_array ( $container )) {
			$container = new Container ( $container );
		}
		if (! $container instanceof ContainerInterface) {
			// exit ( 'Expected a ContainerInterface' );
			throw new \RuntimeException ( 'Expected a ContainerInterface' );
		}
		$this->container = $container;
	}
	public function getContainer() {
		return $this->container;
	}
	public function setContainer($id, $value) {
		return $this->container->offsetSet ( $id, $value );
	}
	public function ajax() {
		return $this->request ()->isXmlHttpRequest ();
	}
	public function method() {
		return $this->request ()->method ();
	}
	protected function logger() {
		return $this->container->get ( 'logger' );
	}
	protected function request() {
		return $this->container->get ( 'request' );
	}
	protected function router() {
		return $this->container->get ( 'router' );
	}
	protected function getParameter($index, $no_result = FALSE) {
		return $this->request ()->get_post ( $index, $no_result );
	}
	public function info($message = null, array $context = array()) {
		$this->logger ()->info ( $message, $context );
	}
	public function debug($message = null, array $context = array()) {
		$this->logger ()->debug ( $message, $context );
	}
	public function err($message = null, array $context = array()) {
		$this->logger ()->error ( $message, $context );
	}
	/**
	 * 로그인 여부를 체크 할 페이지 인지에 대한 세팅을 한다.
	 * true를 리턴하면 로그인 체크를 하며, false를 리턴할 경우 로그인 체크를 하지 않는다.
	 * 개발자가 해당 페이지가 로그인 해야 하는 페이지라면 true를 리턴 하도록 overriding 한다.
	 */
	protected function requireLogin() {
		return true;
	}
	/**
	 * 로그아웃
	 */
	public function logout() {
		session_unset ();
		session_destroy ();
		unset ( $_SESSION );
		// $this->setRedirect ( $this->_defaultPage );
		$this->router ()->redirect ( $this->_defaultPage );
	}
	protected function setToken() {
		if (function_exists ( 'mcrypt_create_iv' )) {
			$_SESSION ['token'] = bin2hex ( mcrypt_create_iv ( 32, MCRYPT_DEV_URANDOM ) );
		} else {
			$_SESSION ['token'] = bin2hex ( openssl_random_pseudo_bytes ( 32 ) );
		}
		$_SESSION ['token_time'] = time ();
	}
	public function getToken() {
		return if_exists ( $_SESSION, 'token', null );
	}
	protected function verifyCsrfToken() {
		if (! $this->container->get ( 'config' )->get ( 'enableCsrfProtection' )) {
			return true;
		}
		
		if (in_array ( $this->method (), [ 
				'HEAD',
				'GET',
				'OPTIONS' 
		] )) {
			return true;
		}
		
		$token = $this->getParameter ( 'token' );
		// $this->debug ( $token );
		// $this->debug ( $this->getToken () );
		return $this->getToken () === $token;
	}
	public function setUser($user) {
		$_SESSION ['user'] = $user;
	}
	protected function getUser() {
		return if_exists ( $_SESSION, 'user', false );
	}
}