<?php

namespace Kaiser;

class Controller {
	// 타임 스템프
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
	public function info($message) {
		$this->logger ()->info ( $message );
	}
	public function debug($message) {
		$this->logger ()->debug ( $message );
	}
	public function err($message) {
		$this->logger ()->error ( $message );
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
	function logout() {
		session_unset ();
		session_destroy ();
		unset ( $_SESSION );
		// $this->setRedirect ( $this->_defaultPage );
		$this->router ()->redirect ( $this->_defaultPage );
	}
}