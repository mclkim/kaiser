<?php

namespace Mcl\Kaiser;

class Controller extends BaseController implements ControllerInterface
{
    /**
     * 로그인 여부를 체크 할 페이지 인지에 대한 세팅을 한다.
     * true를 리턴하면 로그인 체크를 하며, false를 리턴할 경우 로그인 체크를 하지 않는다.
     * 개발자가 해당 페이지가 로그인 해야 하는 페이지라면 true를 리턴 하도록 overriding 한다.
     */
    function requireLogin()
    {
        return false;
    }

    function requireAdmin()
    {
        return false;
    }

    function execute()
    {
        echo 'Hello Kaiser PHP framework~~';
    }

    /**
     * 로그아웃
     */
    function logout()
    {
        session_unset();
        session_destroy();
        unset ($_SESSION);

        $auth = new \Mcl\Kaiser\Auth();
        $this->redirect($auth->_defaultPage);
    }

    protected function ajax()
    {
        return $this->request()->isXhr();
    }

    protected function method()
    {
        return $this->request()->method();
    }

    protected function header($key = null, $alt = null)
    {
        return $this->request()->header($key = null, $alt = null);
    }

    protected function getParameters()
    {
        return $this->request()->get() + $this->request()->post();
    }

    protected function getPostParameter($key = null, $alt = null)
    {
        return $this->request()->post($key, $alt);
    }

    protected function getParameter($index, $no_result = FALSE)
    {
        return $this->request()->get_post($index, $no_result);
    }

    protected function redirect($location, $code = 302, $phrase = null)
    {
        return $this->response()->redirect($location, $code, $phrase);
    }

    protected function status($code, $phrase = null, $version = null)
    {
        return $this->response()->status($code, $phrase, $version);
    }

    function info($message = null, array $context = array())
    {
        $message = is_array($message) ? var_export($message, true) : $message;
        $this->logger()->info($message, $context);
    }

    function debug($message = null, array $context = array())
    {
        $message = is_array($message) ? var_export($message, true) : $message;
        $this->logger()->debug($message, $context);
    }

    function err($message = null, array $context = array())
    {
        $message = is_array($message) ? var_export($message, true) : $message;
        $this->logger()->error($message, $context);
    }
}
