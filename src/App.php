<?php

namespace Kaiser;

use \Kaiser\Exception\SystemException;

class App extends Controller {
	const VERSION = '16.03.18';
	// 타임 스템프
	protected $timestamp = null;
	static $AppDirectory;
	static $basePath;
	function __construct($container = []) {
		parent::__construct ( $container );
		$this->info ( sprintf ( 'The Class "%s" Initialized ', get_class ( $this ) ) );
		/**
		 * 타임스템프를 기록..
		 */
		$this->timestamp = new \Kaiser\Timer ();
	}
	function __destruct() {
		/**
		 * 타임스템프를 기록한 시간 차이를 계산하여 출력한다.
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
// logger()		
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
		 * Execute AJAX event
		 */
		if ($ajaxResponse = $this->execAjaxHandlers ()) {
			$this->debug ( $ajaxResponse );
			// echo $ajaxResponse;
			return $ajaxResponse;
		}
		
		/**
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
				
				/**
				 * Execute the handler
				 */
				if (! $result = $this->runAjaxHandler ( $callable )) {
					throw new ApplicationException ( 'execAjaxHandlers' );
				}
				
				$responseContents = [ ];
				
				/**
				 * If the handler returned an array, we should add it to output for rendering.
				 * If it is a string, add it to the array with the key "result".
				 */
				if (is_array ( $result )) {
					$responseContents = array_merge ( $responseContents, $result );
				} elseif (is_string ( $result )) {
					$responseContents ['result'] = $result;
				}
// $this->debug($responseContents);				
				// header ( 'HTTP/1.1 200 OK' );
				// header ( 'Content-Type: application/json' );
				// return json_encode ( $responseContents );
				return Response::getInstance()->setContent($responseContents);
			}
            catch (ValidationException $ex) {
                /*
                 * Handle validation error gracefully
                 */
                Flash::error($ex->getMessage());
                $responseContents = [];
                $responseContents['#layout-flash-messages'] = $this->makeLayoutPartial('flash_messages');
                $responseContents['X_OCTOBER_ERROR_FIELDS'] = $ex->getFields();
                throw new AjaxException($responseContents);
            }
            catch (MassAssignmentException $ex) {
                throw new ApplicationException(Lang::get('backend::lang.model.mass_assignment_failed', ['attribute' => $ex->getMessage()]));
			} catch ( Exception $ex ) {
				throw $ex;
			}
		}
		
		return null;
	}
	private function check($callable) {
// 		$this->debug ( $callable );
// 		$this->debug ( $callable [0]->requireLogin () );
// 		$this->debug ( $this->user );
		
		if ($callable [0]->requireLogin ()) {
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
				return true;
			}
			return false;
		}
		return true;
	}
	private function runAjaxHandler($callable) {
		try {
			$result = null;
			
			/**
			 * Not logged in, redirect to login screen or show ajax error.
			 * 로그인 여부를 체크 할 페이지 인지 확인한다.
			 * TODO::다른 방법이 있을 것 같은데~
			 */
			if (! $this->check ( $callable )) {
				$returnURI = $callable [0]->getParameter ( 'returnURI', $_SERVER ['REQUEST_URI'] );
				$redirect = implode ( "/", array_map ( "rawurlencode", explode ( "/", $returnURI ) ) );
				return $this->ajax () ? '액세스가 거부되었습니다' : $this->router ()->redirect ( $this->_loginPage . '&returnURI=' . $redirect );
			}
			
			if (! method_exists ( $callable [0], $callable [1] )) {
				throw new SystemException ( sprintf ( "Action %s is not found in the controller %s", $callable [1], $callable [0] ) );
			}
			
			/**
			 * Execute the handler
			 */
			$this->info ( sprintf ( 'The Class "%s" does "%s" method', get_class ( $callable [0] ), $callable [1] ) );
			$result = call_user_func_array ( $callable, [ ] );
			$this->debug($result);
			return ($result) ?  : true;
		} catch ( \Exception $e ) {
			$this->err ( $e );
			// $e = new \Kaiser\Exception\DefaultException ( $e );
			// $e->displayError ();
		}
		
		return false;
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
		// $this->debug($callable);
		/**
		 * 클래스 인스턴스를 실행한다.
		 */
		if (! $result = $this->runAjaxHandler ( $callable )) {
			// throw new ApplicationException ( 'execPageAction' );
			// return false;
		}
		// $this->debug($result);
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