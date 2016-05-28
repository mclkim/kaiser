<?php

namespace Kaiser;

use Kaiser\Request;

class Router extends Singleton
{
    protected $query;
    protected $route;
    protected $param;

    function __construct()
    {
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
    private function __URIPath($url)
    {
        preg_match('%^(.*?)[\\\\/]*(([^/\\\\]*?)(\.([^\.\\\\/]+?)|))[\\\\/\.]*$%im', $url, $m);
        return array(
            'dirname' => isset ($m [1]) ? $m [1] : '',
            'basename' => isset ($m [2]) ? $m [2] : '',
            'extension' => isset ($m [5]) ? $m [5] : '',
            'filename' => isset ($m [3]) ? $m [3] : ''
        );
    }

    function setQuery($query)
    {
        if (strpos($query, '::')) {
            $query = str_replace('::', '.', $query);
        }
        $this->query = $query;
    }

    protected function getQueryString()
    {
        if ($this->query)
            return $this->query;

        return $this->query = Request::getInstance()->url(PHP_URL_QUERY);
    }

    private function splitQueryString($query)
    {
        return $query ? explode('&', $query) : array();
    }

    public function getRoute()
    {
        if ($this->route)
            return $this->route;

        $action = if_empty($_GET, 'action', null);
        $query = $this->splitQueryString($this->getQueryString());
        $param = $this->param = array_slice($query, 1);
//        var_dump($action);
//        exit;
        if (isset($action)) {
            $x = isset ($action) ? $this->__URIPath($action) : array();
        } else {
            $x = isset ($query [0]) ? $this->__URIPath($query [0]) : array();
        }
        $this->route = new \stdClass ();
        $this->route->path = if_empty($x, 'dirname', '');
        $this->route->class = if_empty($x, 'filename', 'index');
        $this->route->action = if_empty($x, 'extension', 'execute');
        $this->route->controller = rtrim($this->route->path, '/') . '/' . $this->route->class;
        $this->route->param = $param;

        return $this->route;
    }

    public function getCurrentUrl()
    {
        return Request::getInstance()->url();
    }

    public function getBaseUrl($atRoot = FALSE)
    {
        $http = Request::getInstance()->url(PHP_URL_SCHEME);
        $host = Request::getInstance()->url(PHP_URL_HOST);
        $port = Request::getInstance()->url(PHP_URL_PORT);
        $path = $atRoot ? '' : Request::getInstance()->url(PHP_URL_PATH);

        $tmplt = $port ? ($path ? "%s://%s:%d%s" : "%s://%s:%d") : ($path ? "%s://%s/%s" : "%s://%s");

        return sprintf($tmplt, $http, $host, $port, $path);
    }

    public static function baseUrl()
    {
    }

    public static function currentUrl()
    {
    }
}
