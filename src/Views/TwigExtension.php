<?php
/**
 * Created by PhpStorm.
 * User: 김준수
 * Date: 2018-07-17
 * Time: 오전 10:26
 */

namespace Mcl\Kaiser\Views;

class TwigExtension extends \Twig_Extension
{
    private $uri;

    public function __construct($uri)
    {
        $this->uri = $uri;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('base_url', array($this, 'baseUrl')),
        ];
    }

    public function baseUrl()
    {
        if (is_string($this->uri)) {
            return $this->uri;
        } else if (method_exists($this->uri, 'getBaseUrl')) {
            return $this->uri->getBaseUrl();
        } else if (method_exists($this->uri->getUri(), 'getBaseUrl')) {
            return $this->uri->getUri()->getBaseUrl();
        }
    }
}