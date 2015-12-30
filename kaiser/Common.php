<?php

namespace Kaiser;

/**
 * ---------------------------------------------------------------
 * 프로그램 개발환경에 대한 정보 phpinfo();
 * ---------------------------------------------------------------
 */
// TODO::
defined ( 'DEV_APACHE' ) or define ( 'DEV_APACHE', 'Apache/2.4.14' );
defined ( 'DEV_PHP' ) or define ( 'DEV_PHP', 'PHP/5.5.30' );
defined ( 'DEV_MYSQL' ) or define ( 'DEV_MYSQL', 'Mysql/5.6.26' );
/**
 * ---------------------------------------------------------------
 * 프로그램에서만 사용하는 경로
 * ---------------------------------------------------------------
 */
// TODO::
defined ( 'DS' ) or define ( 'DS', '/' );
// defined ( 'APP_PATH' ) or define ( 'APP_PATH', BASE_PATH . '/app' );

/**
 * ---------------------------------------------------------------
 * ERROR REPORTING
 * ---------------------------------------------------------------
 * 다른 환경 오류보고의 다른 수준을 필요로 할 것이다.
 * 기본 개발하여 오류하지만 테스트를 표시하고 살 것이다 숨길 것입니다.
 *
 * development : 모든 에러내용을 화면에 출력
 * testing : 화면에 에러 출력하지 않고 에러로그에 출력
 * production : 화면에도 에러로그에도 출력하지 않는다.
 *
 * ; error_reporting
 * ; Development Value: E_ALL
 * ; Default Value: E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED
 * ; Production Value: E_ALL & ~E_DEPRECATED & ~E_STRICT
 */
// TODO::
if (defined ( 'ENVIRONMENT' )) {
	switch (ENVIRONMENT) {
		case 'development' :
			error_reporting ( E_ALL );
			ini_set ( 'display_errors', TRUE );
			ini_set ( 'display_startup_errors', TRUE );
			break;
		case 'testing' :
			error_reporting ( E_ALL & ~ E_NOTICE & ~ E_STRICT & ~ E_DEPRECATED );
			ini_set ( 'display_errors', 'off' );
			break;
		case 'production' :
			error_reporting ( E_ALL & ~ E_STRICT & ~ E_DEPRECATED );
			ini_set ( 'display_errors', 'off' );
			break;
		default :
			exit ( 'The application environment is not set correctly.' );
	}
}
/**
 * ---------------------------------------------------------------
 * 한국시간(timezone)설정
 * [출처] http://kr2.php.net/manual/en/timezones.php
 * ---------------------------------------------------------------
 */
if (function_exists ( 'date_default_timezone_set' )) {
	date_default_timezone_set ( "Asia/Seoul" );
}
ini_set ( 'arg_separator.output', '&amp;' );
/**
 * ---------------------------------------------------------------
 * PHP의 현재 버전이 제공된 값보다 큰 경우 * 결정
 *
 * 우리는 조건부 (PHP>5)를 테스트 몇 가지 장소가 있기 때문에 우리는 정적 변수를 설정합니다.
 * ---------------------------------------------------------------
 */
if (! function_exists ( 'is_php' )) {
	function is_php($version = '5.0.0') {
		static $_is_php;
		$version = ( string ) $version;
		
		if (! isset ( $_is_php [$version] )) {
			$_is_php [$version] = (version_compare ( PHP_VERSION, $version ) < 0) ? FALSE : TRUE;
		}
		
		return $_is_php [$version];
	}
}
/**
 * ------------------------------------------------------
 * PHP의 오류를 기록 할 수있는 사용자 지정 오류 처리기를 정의
 * ------------------------------------------------------
 */
// set_error_handler('_exception_handler');
if (! is_php ( '5.3' )) {
	@set_magic_quotes_runtime ( 0 ); // Kill magic quotes
}

/**
 * ---------------------------------------------------------------
 * Class registry
 *
 * This function acts as a singleton. If the requested class does not
 * exist it is instantiated and set to a static variable. If it has
 * previously been instantiated the variable is returned.
 * 클래스 레지스트리
 *
 * 이 기능은 단일 역할을합니다.
 * 요구 된 클래스가없는 경우 이 정적 변수로 인스턴스화 설정되어 존재한다.
 * 인스턴스이있는 경우 이전에 변수가 반환됩니다 .
 * ---------------------------------------------------------------
 */
class Common {
	protected $directory;
	function __construct($directory) {
		if (! is_php ( '5.5' )) {
			exit ( 'Kaiser Framework support higher than PHP 5.5' );
		}
		$this->directory = is_array ( $directory ) ? $directory : array (
				$directory 
		);
	}
	function load_class($class, $directory = APP_PATH) {
		// static $_classes = array ();
		
		// Does the class exist? If so, we're done...
		// if (isset ( $_classes [$class] )) {
		// return $_classes [$class];
		// }
		
		// Look for the class first in the local application/libraries folder
		// then in the native system/libraries folder
		foreach ( $this->directory as $path ) {
			// var_dump ( $this->directory );
			// var_dump ( $path . DS . $directory . DS . $class . '.php' );
			if (file_exists ( $path . DS . $directory . DS . $class . '.php' )) {
				if (class_exists ( $class ) === FALSE) {
					require ($path . DS . $directory . DS . $class . '.php');
				}
				break;
			}
		}
		
		if (file_exists ( $directory . DS . $class . '.php' )) {
			if (class_exists ( $class ) === FALSE) {
				require ($directory . DS . $class . '.php');
			}
		}
		
		// Did we find the class?
		if (class_exists ( $class, false ) === FALSE) {
			// Note: We use exit() rather then show_error() in order to avoid a
			// self-referencing loop with the Excptions class
			// exit ( 'Unable to locate the specified class: ' . $class . '.php' );
			throw new \Kaiser\Exception\PageNotFound ( 'Unable to locate the specified class: ' . $class . '.php' );
		}
		
		// // Keep track of what we just loaded
		// is_loaded ( $class );
		
		// $_classes [$class] = new $class ();
		// return $_classes [$class];
	}
}

/**
 * ---------------------------------------------------------------
 * Keeps track of which libraries have been loaded.
 * This function is
 * called by the load_class() function above
 * ---------------------------------------------------------------
 */
if (! function_exists ( 'is_loaded' )) {
	function &is_loaded($class = '') {
		static $_is_loaded = array ();
		
		if ($class != '') {
			$_is_loaded [strtolower ( $class )] = $class;
		}
		
		return $_is_loaded;
	}
}

