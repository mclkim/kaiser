<?php

namespace Mcl\Kaiser;


class Route
{
    private $url;
    private $methods = array(
        'GET',
        'POST',
        'PUT',
        'DELETE',
    );
    var $path;
    var $class;
    var $action;
    var $controller;
    var $parameters = array();

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->methods = isset($config['methods']) ? (array)$config['methods'] : array();
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $url = (string)$url;
        $this->url = $url;
    }

    public function getMethods()
    {
        return $this->methods;
    }

    public function setMethods(array $methods)
    {
        $this->methods = $methods;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters)
    {
        $this->parameters += $parameters;
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

    public function getRoute($uri)
    {
        $this->setUrl($uri);
        $query = $uri = $this->getUrl();

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
        $this->path = if_empty($x, 'dirname', '');
        $this->class = if_empty($x, 'filename', 'index');
        $this->action = if_empty($x, 'extension', 'execute');
        $this->controller = rtrim($this->path, '/') . '/' . $this->class;
//        var_dump($this);exit;
        return $this;
    }

    public function dispatch()
    {
        if (!is_null($this->action)) {
            $handler = new $this->controller;
            if (is_callable(array($handler, $this->action))) {
                call_user_func_array(array($handler, $this->action), $this->parameters);
            }
        } else {
            $handler = new $this->controller($this->parameters);
        }
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