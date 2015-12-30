<?php

namespace Kaiser;

class App extends Controller {
	// 타임 스템프
	protected $timestamp = null;
	protected $callable;
	protected $params = array ();
	function __construct($container = []) {
		parent::__construct ( $container );
		
		$this->info ( '-------------------------------------' );
		$this->info ( sprintf ( 'The Class "%s" Initialized ', get_class ( $this ) ) );
		$this->timestamp = new Timer ();
	}
	function __destruct() {
		/**
		 * 타입스템프를 기록한 시간 차이를 계산하여 출력한다.
		 */
		$this->info ( sprintf ( 'The Class "%s" total execution time: ', get_class ( $this ) ) . $this->timestamp->fetch () );
	}
	function run() {
		try {
			/**
			 * URL의 라우팅 설정
			 * 실행파일의 클래스명과 실행메소드를 분리하여 구한다.
			 */
			$router = $this->router ()->getRoute ();
			
			/**
			 * 클래스명과 파일 경로를 전달받아 클래스 인스턴스를 생성한다.
			 */
			$callable = $this->resolve ( $router );
			
			/**
			 * 클래스 인스턴스를 실행한다.
			 */
			$this->dispath ( $callable );
		} catch ( \Kaiser\Exception\AlertException $e ) {
			$this->error ( $e );
		} catch ( \Kaiser\Exception\CallException $e ) {
			$this->error ( $e );
		} catch ( \Kaiser\Exception\DBException $e ) {
			$this->error ( $e );
		} catch ( \Kaiser\Exception\EchoException $e ) {
			$this->error ( $e );
		} catch ( \Kaiser\Exception\FileException $e ) {
			$this->error ( $e );
		} catch ( \Kaiser\Exception\FtpException $e ) {
			$this->error ( $e );
		} catch ( \Kaiser\Exception\LoginException $e ) {
			$this->error ( $e );
		} catch ( \Kaiser\Exception\PageNotFound $e ) {
			$this->error ( $e );
		} catch ( \Exception $e ) {
			$this->err ( $e );
			$e = new \Kaiser\Exception\DefaultException ( $e );
			$e->displayError ();
		}
	}
	private function dispath($callable) {
		/**
		 * 세션스타트..
		 */
		$callable [0]->container->get ( 'session' );
		
		/**
		 * TODO::다른 방법이 있을 것 같은데~
		 * 로그인 여부를 체크 할 페이지 인지 확인한다.
		 */
		if ($callable [0]->requireLogin ()) {
			
			$returnURI = $callable [0]->getParameter ( 'returnURI', $_SERVER ['REQUEST_URI'] );
			$redirect = urlencode ( $returnURI );
			
			if (if_exists ( $_SESSION, 'auth', false ) !== true) {
				$callable [0]->router ()->redirect ( $this->_loginPage . '&returnURI=' . $redirect );
				return;
			}
		}
		
		/**
		 * 클래스 인스턴스를 실행한다.
		 */
		if ($callable instanceof Closure) {
			$callable = $callable->bindTo ( $this->container );
		}
		$this->info ( sprintf ( 'The Class "%s" does "%s" method', get_class ( $callable [0] ), $callable [1] ) );
		
		$callable ();
	}
	private function resolve($toResolve) {
		$resolved = $toResolve;
		
		$path = $toResolve->path;
		$class = $toResolve->controller;
		$method = $toResolve->action;
		
		$common = new \Kaiser\Common ();
		$common->load_class ( $class, $path );
		
		$resolved = [ 
				new $class ( $this->container ),
				$method 
		];
		
		if (! is_callable ( $resolved )) {
			// throw new RuntimeException ( sprintf ( '%s is not resolvable', $toResolve ) );
			throw new \Kaiser\Exception\CallException ( sprintf ( 'The required method "%s" does not exist for %s', $method, $class ) );
		}
		
		return $resolved;
	}
	/**
	 * 사용자 에러메시지
	 */
	private function error(\Exception $e) {
		$this->err ( $e );
		$e->displayError ();
	}
}