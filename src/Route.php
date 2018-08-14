<?php

namespace Mcl\Kaiser;


class Route
{
    var $path;
    var $class;
    var $action;
    var $controller;
    var $parameters = array();
    private $url;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getRoute()
    {
        $query = $uri = $this->url;

        if (($pos = strpos($uri, '&')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        $query = ($query) ? explode('&', $query) : array();
        $params = array_slice($query, 1);

        $param = [];
        foreach ($params as $p) {
            $paramData = explode("=", $p);
            if ($paramData[0]) $param[$paramData[0]] = $paramData[1];
        }
        $this->parameters = $param;

        $x = $this->__URIPath($uri);
        $this->path = empty($x['dirname']) ? '' : $x['dirname'];
        $this->class = empty($x['filename']) ? 'index' : $x['filename'];
        $this->action = empty($x['extension']) ? 'execute' : $x['extension'];
        $this->controller = trim($this->path, '/') . '/' . $this->class;
        return $this;
    }

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

    public function getPath()
    {
        return $this->path;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getController()
    {
        return $this->controller;
    }
}