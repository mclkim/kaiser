<?php

namespace Kaiser;

class Router {
	protected $query;
	protected $uri;
	protected $param;
	function __construct() {
	}
	function getRoute() {
		if ($this->uri)
			return $this->uri;
		
		$query = $this->splitQueryString ( $this->getQueryString () );
		
		$x = isset ( $query [0] ) ? $this->__URIPath ( $query [0] ) : array ();
		
		$this->uri = new \stdClass ();
		$this->uri->path = if_empty ( $x, 'dirname', '' );
		$this->uri->controller = if_empty ( $x, 'filename', 'index' );
		$this->uri->action = if_empty ( $x, 'extension', 'execute' );
		$this->uri->file = rtrim ( $this->uri->path, '/' ) . '/' . $this->uri->controller . '.php';
		
		return $this->uri;
	}
	/**
	 * 클래스명 앞에 경로(/)가 있을경우를
	 * 경로명과 클래스명을 분리하여 처리 한다.(2014.02.28)
	 * C:\>php -r "print_r(__URIPath('/mnt/files/한글.mp3'));"
	 * Array
	 * (
	 * [dirname] => /mnt/files
	 * [basename] => 한글.mp3
	 * [extension] => mp3
	 * [filename] => 한글
	 * )
	 */
	private function __URIPath($url) {
		preg_match ( '%^(.*?)[\\\\/]*(([^/\\\\]*?)(\.([^\.\\\\/]+?)|))[\\\\/\.]*$%im', $url, $m );
		return array (
				'dirname' => isset ( $m [1] ) ? $m [1] : '',
				'basename' => isset ( $m [2] ) ? $m [2] : '',
				'extension' => isset ( $m [5] ) ? $m [5] : '',
				'filename' => isset ( $m [3] ) ? $m [3] : '' 
		);
	}
	function setQuery($query) {
		if (strpos ( $query, '::' )) {
			$query = str_replace ( '::', '.', $query );
		}
		$this->query = $query;
	}
	protected function getQueryString() {
		if ($this->query)
			return $this->query;
		
		return $this->query = urldecode ( preg_replace ( '/\?.*$/', '', $_SERVER ['QUERY_STRING'] ) );
	}
	private function splitQueryString($query) {
		$query = parse_url ( trim ( $query, '/' ), PHP_URL_PATH );
		return $query ? explode ( '&', $query ) : array ();
	}
	public function redirect($url, $http_code = 303) {
		// Turn relative URL into absolute URL
		if (strpos ( $url, '://' ) === false) {
			if ($url == '' || $url [0] != '/')
				$url = dirname ( $_SERVER ['SCRIPT_NAME'] ) . '/' . $url;
				// $url = rtrim ( $_SERVER ['REQUEST_URI'], '/' ) . '/' . $url;
				// phpinfo();
				// var_dump($url);exit;
			
			$url = (empty ( $_SERVER ['HTTPS'] ) ? 'http://' : 'https://') . $_SERVER ['HTTP_HOST'] . $url;
		}
		
		if (ob_get_level () > 1)
			ob_end_clean ();
			
			// var_dump($url);exit;
		header ( "Location: $url", true, $http_code );
		
		echo 'You are being redirected to <a href="' . $url . '">' . $url . '</a>';
		exit ();
	}
	function getCurrentUrl() {
		$protocol = ! empty ( $_SERVER ['HTTPS'] ) ? 'https://' : 'http://';
		
		$params = array ();
		if (isset ( $_SERVER ['QUERY_STRING'] ) && ! empty ( $_SERVER ['QUERY_STRING'] )) {
			parse_str ( $_SERVER ['QUERY_STRING'], $params );
			$params ['lang'] = 'anything';
			unset ( $params ['lang'] ); // This will clear it from the parameters
		}
		
		// Now rebuild the new URL
		return $url = $protocol . $_SERVER ['SERVER_NAME'] . $_SERVER ['SCRIPT_NAME'] . (! empty ( $params ) ? ('?' . http_build_query ( $params )) : '');
	}
	function getBaseUrl($atRoot = FALSE, $atCore = FALSE, $parse = FALSE) {
		if (isset ( $_SERVER ['HTTP_HOST'] )) {
			$http = isset ( $_SERVER ['HTTPS'] ) && strtolower ( $_SERVER ['HTTPS'] ) !== 'off' ? 'https' : 'http';
			$hostname = $_SERVER ['HTTP_HOST'];
			$dir = str_replace ( basename ( $_SERVER ['SCRIPT_NAME'] ), '', $_SERVER ['SCRIPT_NAME'] );
			
			$core = preg_split ( '@/@', str_replace ( $_SERVER ['DOCUMENT_ROOT'], '', realpath ( dirname ( __FILE__ ) ) ), NULL, PREG_SPLIT_NO_EMPTY );
			$core = $core [0];
			
			$tmplt = $atRoot ? ($atCore ? "%s://%s/%s/" : "%s://%s/") : ($atCore ? "%s://%s/%s/" : "%s://%s%s");
			$end = $atRoot ? ($atCore ? $core : $hostname) : ($atCore ? $core : $dir);
			
			$base_url = sprintf ( $tmplt, $http, $hostname, $end );
		} else
			$base_url = 'http://localhost/';
		
		if ($parse) {
			$base_url = parse_url ( $base_url );
			if (isset ( $base_url ['path'] ))
				if ($base_url ['path'] == '/')
					$base_url ['path'] = '';
		}
		
		return $base_url;
	}
	public static function getLocalReferer() {
		return ! empty ( $_SERVER ['HTTP_REFERER'] ) && parse_url ( $_SERVER ['HTTP_REFERER'], PHP_URL_HOST ) == $_SERVER ['HTTP_HOST'] ? $_SERVER ['HTTP_REFERER'] : null;
	}
	public function urlFor($name, $params = array()) {
	}
	public function siteUrl() {
	}
	public function baseUrl() {
	}
	public function currentUrl() {
	}
}
/**
 * ---------------------------------------------------------------
 * 기본함수
 * ---------------------------------------------------------------
 */
if (! function_exists ( 'if_exists' )) {
	function if_exists($array, $key, $def = null) {
		if (is_array ( $array ) == false) {
			return $def;
		}
		return array_key_exists ( $key, $array ) ? $array [$key] : $def;
	}
}
if (! function_exists ( 'if_empty' )) {
	function if_empty($array, $key, $def = null) {
		$ret = if_exists ( $array, $key, $def );
		return ! empty ( $ret ) ? $ret : $def;
	}
}
