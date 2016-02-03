<?php

namespace Kaiser;

class App extends Controller {
	const VERSION = '5.0.34';
	// 타임 스템프
	protected $timestamp = null;
	protected $AppDirectory;
	function __construct($container = [], $basePath = null) {
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
	public function getAjaxHandler() {
		$request = $this->request ();
		
		if (! $this->ajax () || $this->method () != 'POST') {
			return null;
		}
		
		if ($handler = $request->header ( 'X-October-Request-Handler' )) {
			return trim ( $handler );
		}
		
		return null;
	}
	/**
	 * Execute the controller action.
	 */
	public function run() {
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
			
			try {
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
				
				// $this->debug ( $responseContents );
				
				header ( 'HTTP/1.1 200 OK' );
				header ( 'Content-Type: application/json' );
				return json_encode ( $responseContents );
			} catch ( \Kaiser\Exception\ApplicationException $e ) {
				$this->error ( $e );
			} catch ( \Exception $e ) {
				$this->err ( $e );
				$e = new \Kaiser\Exception\DefaultException ( $e );
				$e->displayError ();
			}
		}
		
		return null;
	}
	protected function runAjaxHandler($callable) {
		// Execute the action
		$result = call_user_func_array ( $callable, [ ] );
		return ($result) ?  : true;
	}
	protected function execPageAction() {
		try {
			$rout = $this->router ();
			
			/**
			 * URL의 라우팅 설정
			 * 실행파일의 클래스명과 실행메소드를 분리하여 구한다.
			 */
			$router = $rout->getRoute ();
			
			/**
			 * 클래스명과 파일 경로를 전달받아 클래스 인스턴스를 생성한다.
			 */
			$callable = $this->resolve ( $router );
			
			// Execute the action
			$result = call_user_func_array ( $callable, [ ] );
			
			return ($result) ?  : true;
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
		
		return null;
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
	function setAppDir($directory = []) {
		$this->AppDirectory = $directory;
	}
	function getAppDir() {
		return $this->AppDirectory;
	}
	/**
	 * This method is used internally.
	 * Finds a backend controller with a callable action method.
	 */
	protected function findController($controller, $action, $inPath) {
		/*
		 * Workaround: Composer does not support case insensitivity.
		 */
		if (! class_exists ( $controller )) {
			$controller = Str::normalizeClassName ( $controller );
			$controllerFile = $inPath . strtolower ( str_replace ( '\\', '/', $controller ) ) . '.php';
			if ($controllerFile = File::existsInsensitive ( $controllerFile )) {
				include_once ($controllerFile);
			}
		}
		
		if (! class_exists ( $controller )) {
			return false;
		}
		
		$controllerObj = App::make ( $controller );
		
		if ($controllerObj->actionExists ( $action )) {
			return $controllerObj;
		}
		
		return false;
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