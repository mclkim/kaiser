<?php

namespace Kaiser;

class Request {
	var $headers = array ();
	var $params = array ();
	var $ip_address = FALSE;
	var $user_agent = FALSE;
	protected $method;
	protected static $_request_headers;
	protected static $httpMethodParameterOverride = false;
	function __construct() {
		$this->headers = self::request_headers ();
		// $this->debug ( sprintf ( 'The Class "%s" Initialized ', get_class ( $this ) ) );
		// 일반 request parameter도 함께 처리하도록
		if (is_array ( $_REQUEST )) {
			foreach ( $_REQUEST as $name => $value ) {
				$this->params [$name] = $value;
			}
		}
	}
	function getParameter($index, $no_result = FALSE) {
		return (! isset ( $this->params [$index] )) ? $no_result : stripslashes ( htmlspecialchars ( $this->params [$index] ) );
	}
	/**
	 * --------------------------------------------------------------------
	 * Fetch from array
	 *
	 * This is a helper function to retrieve values from global arrays
	 * --------------------------------------------------------------------
	 */
	private function _fetch_from_array(&$array, $index = '', $no_result = FALSE) {
// 		$ret = if_exists ( $array, $index, $no_result );
		$ret = (! isset ( $array [$index] )) ? $no_result : stripslashes ( htmlspecialchars ( $array [$index] ) );
		return ! empty ( $ret ) ? $ret : $no_result;
	}
	/**
	 * --------------------------------------------------------------------
	 * Fetch an item from the GET array
	 * --------------------------------------------------------------------
	 */
	function get($index = NULL, $no_result = FALSE) {
		// Check if a field has been provided
		if ($index === NULL and ! empty ( $_GET )) {
			$get = array ();
			
			// loop through the full _GET array
			foreach ( array_keys ( $_GET ) as $key ) {
				$get [$key] = $this->_fetch_from_array ( $_GET, $key, $no_result );
			}
			return $get;
		}
		
		return $this->_fetch_from_array ( $_GET, $index, $no_result );
	}
	/**
	 * --------------------------------------------------------------------
	 * Fetch an item from the POST array
	 * --------------------------------------------------------------------
	 */
	function post($index = NULL, $no_result = FALSE) {
		// Check if a field has been provided
		if ($index === NULL and ! empty ( $_POST )) {
			$post = array ();
			
			// Loop through the full _POST array and return it
			foreach ( array_keys ( $_POST ) as $key ) {
				$post [$key] = $this->_fetch_from_array ( $_POST, $key, $no_result );
			}
			return $post;
		}
		
		return $this->_fetch_from_array ( $_POST, $index, $no_result );
	}
	/**
	 * --------------------------------------------------------------------
	 * Fetch an item from either the GET array or the POST
	 * --------------------------------------------------------------------
	 */
	function get_post($index = '', $no_result = FALSE) {
		if (! isset ( $_POST [$index] )) {
			return $this->get ( $index, $no_result );
		} else {
			return $this->post ( $index, $no_result );
		}
	}
	/**
	 * --------------------------------------------------------------------
	 * Fetch an item from the COOKIE array
	 * --------------------------------------------------------------------
	 */
	function cookie($index = '', $no_result = FALSE) {
		return $this->_fetch_from_array ( $_COOKIE, $index, $no_result );
	}
	/**
	 * --------------------------------------------------------------------
	 * Set cookie
	 *
	 * Accepts six parameter, or you can submit an associative
	 * array in the first parameter containing all the values.
	 * --------------------------------------------------------------------
	 */
	// function set_cookie($name = '', $value = '', $expire = '', $domain = '', $path = '/', $prefix = '', $secure = FALSE) {
	function set_cookie($name, $value = null, $expire = null, $path = '/', $domain = null, $secure = FALSE, $httponly = null) {
		if (is_array ( $name )) {
			// always leave 'name' in last place, as the loop will break otherwise, due to $$item
			foreach ( array (
					'value',
					'expire',
					'domain',
					'path',
					'prefix',
					'secure',
					'name' 
			) as $item ) {
				if (isset ( $name [$item] )) {
					$$item = $name [$item];
				}
			}
		}
		
		if (! is_numeric ( $expire )) {
			$expire = time () - 86500;
		} else {
			$expire = ($expire > 0) ? time () + $expire : 0;
		}
		
		// setcookie ( $prefix . $name, $value, $expire, $path, $domain, $secure );
		setcookie ( $name, $value, $expire, $path, $domain, $secure, $httponly );
	}
	/**
	 * --------------------------------------------------------------------
	 * Fetch an item from the SESSION array
	 * --------------------------------------------------------------------
	 */
	function session($index = '', $no_result = FALSE) {
		return $this->_fetch_from_array ( $_SESSION, $index, $no_result );
	}
	/**
	 * --------------------------------------------------------------------
	 * Fetch an item from the SERVER array
	 * --------------------------------------------------------------------
	 */
	function server($index = '', $no_result = FALSE) {
		return $this->_fetch_from_array ( $_SERVER, $index, $no_result );
	}
	/**
	 * --------------------------------------------------------------------
	 * Fetch the IP Address
	 * --------------------------------------------------------------------
	 */
	function ip_address() {
		if ($this->ip_address !== FALSE) {
			return $this->ip_address;
		}
		
		if (isset ( $_SESSION ['client_addr'] ))
			return $this->ip_address = $_SESSION ['client_addr'];
		
		$this->ip_address = (isset ( $_SERVER ['REMOTE_ADDR'] )) ? $_SERVER ['REMOTE_ADDR'] : FALSE;
		
		return $this->ip_address;
	}
	/**
	 * --------------------------------------------------------------------
	 * User Agent
	 * --------------------------------------------------------------------
	 */
	function user_agent() {
		if ($this->user_agent !== FALSE) {
			return $this->user_agent;
		}
		
		if (isset ( $_SESSION ['client_agent'] ))
			return $this->user_agent = $_SESSION ['client_agent'];
		
		$this->user_agent = (isset ( $_SERVER ['HTTP_USER_AGENT'] )) ? $_SERVER ['HTTP_USER_AGENT'] : FALSE;
		
		return $this->user_agent;
	}
	/**
	 * @TODO::
	 */
	function getMethod() {
		if (null === $this->method) {
			$this->method = strtoupper ( $this->server ( 'REQUEST_METHOD', 'GET' ) );
			
			if ('POST' === $this->method) {
				if ($method = $this->header ( 'X-HTTP-METHOD-OVERRIDE' )) {
					$this->method = strtoupper ( $method );
				} elseif (self::$httpMethodParameterOverride) {
					$this->method = strtoupper ( $this->request->get ( '_method', $this->query->get ( '_method', 'POST' ) ) );
				}
			}
		}
		
		return $this->method;
	}
	function isXmlHttpRequest() {
		return 'XMLHttpRequest' == $this->header ( 'X-Requested-With' );
	}
	function header($index = '', $no_result = FALSE) {
		return $this->_fetch_from_array ( $this->headers, $index, $no_result );
	}
	protected static function request_headers() {
		if (! self::$_request_headers) {
			self::$_request_headers = array ();
			foreach ( $_SERVER as $name => $value ) {
				if (preg_match ( '/^HTTP_/', $name )) {
					$name = strtolower ( substr ( $name, 5 ) );
					$name = ucwords ( str_replace ( '_', ' ', $name ) );
					$name = str_replace ( ' ', '-', $name );
					self::$_request_headers [$name] = $value;
				}
			}
			// self::$_request_headers ['X-Requested-With'] = 'PHPHttpRequest';
			// self::$_request_headers ['Connection'] = 'close';
		}
		return self::$_request_headers;
	}
}
