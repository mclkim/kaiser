<?php

namespace Kaiser;

class App extends Controller {
	const VERSION = '16.03.18';
	// 타임 스템프
	protected $timestamp = null;
	static $AppDirectory;
	static $basePath;
	function __construct($container = []) {
		parent::__construct ( $container );
		$this->info ( sprintf ( 'The Class "%s" Initialized ', get_class ( $this ) ) );
		$this->timestamp = new \Kaiser\Timer ();
	}
	function __destruct() {
		/**
		 * 타입스템프를 기록한 시간 차이를 계산하여 출력한다.
		 */
		$this->info ( sprintf ( 'The Class "%s" total execution time: ', get_class ( $this ) ) . $this->timestamp->fetch () );
	}
	public function version() {
		return static::VERSION;
	}
	protected function getAjaxHandler() {
		$request = $this->request ();
		
		if (! $this->ajax () || $this->method () != 'POST') {
			return null;
		}
		
		if ($handler = $request->header ( 'X-October-Request-Handler' )) {
			return trim ( $handler );
		}
		
		return null;
	}
	protected function getPostHandler() {
		$request = $this->request ();
		
		if ($this->method () != 'POST') {
			return null;
		}
		
		if ($handler = $request->post ( 'X-October-Request-Handler' )) {
			return trim ( $handler );
		}
		
		return null;
	}
	/**
	 * Execute the controller action.
	 */
	public function run() {
		// phpinfo();exit;
		// $this->debug('------------------');
		// $this->debug($_SERVER);
		// $this->debug($_POST);
		// $this->debug($_REQUEST);
		// $this->debug($_FILES);
		// $this->debug('------------------');
		
		/**
		 * TODO::
		 * 세션스타트..
		 */
		$this->container->get ( 'session' );
		
		if (! $this->getToken ()) {
			$this->setToken ();
		}
		/**
		 * Check security token.
		 */
		if (! $this->verifyCsrfToken ()) {
			throw new ApplicationException ( '잘못된 보안 토큰입니다.' );
			return null;
		}
		/**
		 * Not logged in, redirect to login screen or show ajax error.
		 */
		// if (! \Kaiser\Manager\AuthManager::getInstance ()->check ()) {
		// return $this->ajax () ? '' : $this->router ()->redirect ( $this->_loginPage . '&returnURI=' . $redirect );
		// }
		
		// if (! BackendAuth::check ()) {
		// return Request::ajax () ? Response::make ( '액세스가 거부되었습니다.', 403 ) : Backend::redirectGuest ( 'backend/auth' );
		// }
		
		/*
		 * Execute AJAX event
		 */
		if ($ajaxResponse = $this->execAjaxHandlers ()) {
			// $this->debug ( $ajaxResponse );
			echo $ajaxResponse;
			return null;
		}
		
		/*
		 * Execute page action
		 */
		$result = $this->execPageAction ();
		
		if (! is_string ( $result )) {
			return $result;
		}
	}
	protected function execAjaxHandlers() {
		if ($handler = $this->getAjaxHandler ()) {
			try {
				
				$rout = $this->router ();
				$rout->setQuery ( $handler );
				
				/**
				 * URL의 라우팅 설정
				 * 실행파일의 클래스명과 실행메소드를 분리하여 구한다.
				 */
				$router = $rout->getRoute ();
				
				/**
				 * 클래스명과 파일 경로를 전달받아 클래스 인스턴스를 생성한다.
				 */
				$callable = $this->resolve ( $router );
				
				$responseContents = [ ];
				
				/*
				 * Execute the handler
				 */
				if (! $result = $this->runAjaxHandler ( $callable )) {
					throw new ApplicationException ( 'execAjaxHandlers' );
				}
				
				/*
				 * If the handler returned an array, we should add it to output for rendering.
				 * If it is a string, add it to the array with the key "result".
				 */
				if (is_array ( $result )) {
					$responseContents = array_merge ( $responseContents, $result );
				} elseif (is_string ( $result )) {
					$responseContents ['result'] = $result;
				}
				
				header ( 'HTTP/1.1 200 OK' );
				header ( 'Content-Type: application/json' );
				return json_encode ( $responseContents );
			} catch ( Exception $ex ) {
				throw $ex;
			}
		}
		
		return null;
	}
	protected function runAjaxHandler($callable) {
		try {
			/**
			 * Execute the handler
			 */
			$this->info ( sprintf ( 'The Class "%s" does "%s" method', get_class ( $callable [0] ), $callable [1] ) );
			
			// Execute the action
			$result = call_user_func_array ( $callable, [ ] );
			return ($result) ?  : true;
		} catch ( \Kaiser\Exception\AlertException $e ) {
			$this->error ( $e );
		} catch ( \Kaiser\Exception\ApplicationException $e ) {
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
		
		return null;
	}
	protected function execPageAction() {
		$rout = $this->router ();
		
		if ($handler = $this->getPostHandler ()) {
			$rout->setQuery ( $handler );
		}
		
		/**
		 * URL의 라우팅 설정
		 * 실행파일의 클래스명과 실행메소드를 분리하여 구한다.
		 */
		$router = $rout->getRoute ();
		
		/**
		 * 클래스명과 파일 경로를 전달받아 클래스 인스턴스를 생성한다.
		 */
		$callable = $this->resolve ( $router );
		
		/**
		 * 클래스 인스턴스를 실행한다.
		 */
		if (! $result = $this->runAjaxHandler ( $callable )) {
			throw new ApplicationException ( 'execPageAction' );
			// return false;
		}
		
		return ($result) ?  : true;
	}
	function setAppDir($directory = []) {
		self::$AppDirectory = $directory;
	}
	function getAppDir() {
		return self::$AppDirectory;
	}
	function setBasePath($directory) {
		self::$basePath = $directory;
	}
	function getBasePath() {
		return self::$basePath;
	}
	private function resolve($toResolve) {
		$resolved = $toResolve;
		
		$path = $toResolve->path;
		$class = $toResolve->controller;
		$method = $toResolve->action;
		
		$common = new \Kaiser\Common ( $this->getAppDir () );
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