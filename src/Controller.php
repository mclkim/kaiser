<?php

namespace Kaiser;

class Controller extends BaseController implements ControllerInterface
{
    /**
     * 로그인 여부를 체크 할 페이지 인지에 대한 세팅을 한다.
     * true를 리턴하면 로그인 체크를 하며, false를 리턴할 경우 로그인 체크를 하지 않는다.
     * 개발자가 해당 페이지가 로그인 해야 하는 페이지라면 true를 리턴 하도록 overriding 한다.
     */
    function requireLogin()
    {
        return true;
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
    protected function logout()
    {
        session_unset();
        session_destroy();
        unset ($_SESSION);
        $this->redirect($this->_defaultPage);
    }

    protected function ajax()
    {
        return $this->request()->isXhr();
    }

    protected function method()
    {
        return $this->request()->method();
    }

    protected function getParameters()
    {
//        return array_merge($this->request()->get(), $this->request()->post());
        return $this->request()->get() + $this->request()->post();
    }

    protected function getParameter($index, $no_result = FALSE)
    {
        return $this->request()->get_post($index, $no_result);
    }

    protected function redirect($redirect)
    {
        return $this->response()->redirect($redirect);
    }

    function info($message = null, array $context = array())
    {
        $this->logger()->info($message, $context);
    }

    function debug($message = null, array $context = array())
    {
        $this->logger()->debug($message, $context);
    }

    function err($message = null, array $context = array())
    {
        $this->logger()->error($message, $context);
    }
}
