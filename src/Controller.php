<?php

namespace Kaiser;

class Controller extends BaseController
{
    protected $user;
    var $_defaultPage = '?';
    var $_loginPage = '?login';
    var $_loginAdminPage = '?signin';
    var $_admin = 'admin';
    var $_user = 'user';

    /**
     * 로그인 여부를 체크 할 페이지 인지에 대한 세팅을 한다.
     * true를 리턴하면 로그인 체크를 하며, false를 리턴할 경우 로그인 체크를 하지 않는다.
     * 개발자가 해당 페이지가 로그인 해야 하는 페이지라면 true를 리턴 하도록 overriding 한다.
     */
    protected function requireLogin()
    {
        return true;
    }

    protected function requireAdmin()
    {
        return false;
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

    protected function execute()
    {
        echo 'Hello Kaiser PHP framework~~';
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
        return array_merge($this->request()->get(), $this->request()->post());
    }

    protected function getParameter($index, $no_result = FALSE)
    {
        return $this->request()->get_post($index, $no_result);
    }

    protected function redirect($redirect)
    {
        return $this->response()->redirect($redirect);
    }

    protected function info($message = null, array $context = array())
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

    protected function setUser($user)
    {
        $_SESSION [$this->_user] = $user;
    }

    protected function getUser()
    {
        return if_exists($_SESSION, $this->_user, false);
    }

    protected function setAdmin($admin)
    {
        $_SESSION [$this->_admin] = $admin;
    }

    protected function getAdmin()
    {
        return if_exists($_SESSION, $this->_admin, false);
    }

    protected function setCsrfToken()
    {
        if (function_exists('mcrypt_create_iv')) {
            $_SESSION ['csrf_token'] = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
        } else {
            $_SESSION ['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
        }
        $_SESSION ['token_time'] = time();
    }

    public function getCsrfToken()
    {
        return if_exists($_SESSION, 'csrf_token', null);
    }

    protected function verifyCsrfToken()
    {
        if (!$this->config()->get('enableCsrfProtection')) {
            return true;
        }
        // $this->debug ( $this->container->get ( 'config' )->get ( 'enableCsrfProtection' ) );

        if (in_array($this->method(), [
            'HEAD',
            'GET',
            'OPTIONS'
        ])) {
            return true;
        }

        // $this->debug ( $this->method () );
        // $this->debug ( Request::getInstance ()->header () );

        $csrftoken = Request::getInstance()->header('x-csrf-token');
        // $this->debug ( $csrftoken );

        $token = $this->getParameter('csrf_token', $csrftoken);
        // $this->debug ( $token );
        // $this->debug ( $this->getCsrfToken () );
        return $this->getCsrfToken() === $token;
    }

    protected function check($callable)
    {
        // $this->debug ( $callable );
        // $this->debug ( get_class($callable ));
        // $this->debug ( $callable ->requireLogin () );
        if ($callable->requireLogin()) {
            /**
             * Check supplied session/cookie is an array (username, persist code)
             */
            if ($this->user = $callable->getUser() || $this->user = $callable->getAdmin()) {
                // $this->debug ( $this->user );
                return true;
            }
            return false;
        }
        return true;
    }

    protected function checkAdmin($callable)
    {
        // $this->debug ( $callable );
        // $this->debug ( get_class($callable ));
        // $this->debug ( $callable ->requireLogin () );
        if ($callable->requireAdmin()) {
            /**
             * Check supplied session/cookie is an array (username, persist code)
             */
            if ($this->user = $callable->getAdmin()) {
                // $this->debug ( $this->user );
                return true;
            }
            return false;
        }
        return true;
    }

    protected function alertRedirect($msg, $redirect, $charset = 'utf-8')
    {
        if (!$redirect) {
            echo <<<END
            <head><meta http-equiv="Content-Type" content="text/html; charset=$charset" /></head>
            <script type="text/javascript">
            alert("$msg");
            </script>
END;
            exit ();
        } else {
            echo <<<END
            <head><meta http-equiv="Content-Type" content="text/html; charset=$charset" /></head>
            <script type="text/javascript">
            alert("$msg");
            location.href = "$redirect";
            </script>
END;
            exit ();
        }
    }
}
