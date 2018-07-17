<?php

namespace Mcl\Kaiser;

use Aura\Web\WebFactory;

class Request
{
    protected $request;

    function __construct()
    {
        $factory = new WebFactory (array(
            '_ENV' => $_ENV,
            '_GET' => $_GET,
            '_POST' => $_POST,
            '_COOKIE' => $_COOKIE,
            '_SERVER' => $_SERVER
        ));

        $this->request = $factory->newRequest();
    }

    function cookie($key = null, $alt = null)
    {
        return $this->request->cookies->get($key, $alt);
    }

    function getBaseUrl($port = null)
    {
        $scheme = $this->url(PHP_URL_SCHEME);
        $host = $this->url(PHP_URL_HOST);
        if (!is_null($port)) {
            $port = $this->url(PHP_URL_PORT);
        }

        $host_port = $host . ($port !== null ? ':' . $port : '');

        return ($scheme ? $scheme . ':' : '') . ($host_port ? '//' . $host_port : '');
    }

    function url($component = null)
    {
        return $this->request->url->get($component);
    }

    function method()
    {
        return $this->request->method->get();
    }

    function get_post($key = null, $alt = null)
    {
        $post = $this->post($key);
        return empty ($post) ? $this->get($key, $alt) : $alt;
    }

    function post($key = null, $alt = null)
    {
        return $this->request->post->get($key, $alt);
    }

    function get($key = null, $alt = null)
    {
        return $this->request->query->get($key, $alt);
    }

    function header($key = null, $alt = null)
    {
        return $this->request->headers->get($key, $alt);
    }

    function isXhr()
    {
        return $this->request->isXhr();
    }

    function getContent()
    {
        return $this->request->content->getRaw();
    }
}
