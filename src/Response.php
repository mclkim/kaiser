<?php

namespace Kaiser;

use Aura\Web\WebFactory;
use Aura\Web\ResponseSender;

class Response extends Singleton
{
    protected $response;
    protected $response_sender;
    protected $original;

    function __construct()
    {
        $globals = array();
        $factory = new WebFactory ($globals);
        $this->response = $factory->newResponse();
        $this->response_sender = new ResponseSender ($this->response);
    }

    protected function morphToJson($content)
    {
        if ($content instanceof Jsonable)
            return $content->toJson();

        return json_encode($content);
    }

    protected function shouldBeJson($content)
    {
        return $content instanceof Jsonable || $content instanceof ArrayObject || is_array($content);
    }

    function setContent($content)
    {
        $this->original = $content;

        // If the content is "JSONable" we will set the appropriate header and convert
        // the content to JSON. This is useful when returning something like models
        // from routes that will be automatically transformed to their JSON form.
        if ($this->shouldBeJson($content)) {
            $this->response->headers->set('Content-Type', 'application/json');
            $content = $this->morphToJson($content);
        }

        // If this content implements the "Renderable" interface then we will call the
        // render method on the object so we will avoid any "__toString" exceptions
        // that might be thrown and have their errors obscured by PHP's handling.
        elseif ($content instanceof Renderable) {
            $content = $content->render();
        }

        $this->response->content->set($content);
        return $this->response_sender->__invoke();
    }

    function redirect($location, $code = 302, $phrase = null)
    {
        $this->response->redirect->to($location, $code, $phrase);
        return $this->response_sender->__invoke();
    }

    function getJSON($content, $options = JSON_UNESCAPED_UNICODE)
    {
        $this->response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        $content = json_encode($content, $options);
        $this->response->content->set($content);
        return $this->response_sender->__invoke();
    }
    function getTEXT($content)
    {
        $this->response->headers->set('Content-Type', 'text/plain; charset=ISO-8859-1');
        $this->response->content->set($content);
        return $this->response_sender->__invoke();
    }
}
