<?php
/**
 * Created by PhpStorm.
 * User: 김명철
 * Date: 2018-04-22
 * Time: 오후 3:34
 */

namespace Kaiser;

use Kaiser\Exception\ApplicationException;
use Kaiser\Exception\AjaxException;

class Appp extends Controller
{
    const VERSION = '2018-04-22';
    var $timestamp = null;

    function __construct($container = [])
    {
        $this->setContainer($container);
    }

    protected function start()
    {
        /**
         * 시작을 로그파일에 기록한다.
         */
        $this->info(sprintf('<<START>>The Class "%s" Initialized ', get_class($this)));
        /**
         * 타임스템프를 기록..
         */
        $this->timestamp = new \Kaiser\Timer ();
    }

    protected function end()
    {
        /**
         * 타임스템프를 기록한 시간 차이를 계산하여 기록한다.
         * 사용한 메모리를 기록한다.
         */
        $this->info(sprintf('<<END>>The Class "%s" total execution time: ', get_class($this)) . $this->timestamp->fetch() . ", Memory used: " . bytesize(memory_get_peak_usage()));
    }

    public function version()
    {
        return self::VERSION;
    }

    public function run($directory = [])
    {
        //        phpinfo();exit;
        $this->start();
        /**
         * 세션스타트..
         */
//        $this->container->get('session');
        session_start();

        $this->execPageAction($directory);
        $this->end();
    }

    protected function execPageAction($directory = [])
    {
        $router = new \Kaiser\Router();
        $router->setAppDir($directory);
        $routeInfo = $router->dispatch(array('methods' => ['GET', 'POST']));

        //TODO::
        $controller = $routeInfo[1];
        $action = $routeInfo[2];
        $parameters = $routeInfo[3];

        switch ($routeInfo[0]) {
            case Router::NOT_FOUND:
                // ... 404 Not Found
                $this->err(sprintf('The Class "%s" does not found', $controller));
                header('HTTP/1.0 404 Not Found');
                break;
            case Router::NOT_FOUND_ACTION:
                // ... 405 Method Not Allowed
                $this->err(sprintf("The Action '%s' is not found in the controller '%s'", $action, $controller));
                header('HTTP/1.0 404 Not Found');
                break;
            case Router::FOUND:
                //TODO::
                $instance = new $controller;
                $instance->setContainer($this->getContainer());

                //TODO::
                $auth = new \Kaiser\Auth();
                if (!$auth->checkAdmin($instance)) {
                    $request_uri = if_exists($_SERVER, 'X_HTTP_ORIGINAL_URL', $_SERVER ['REQUEST_URI']);
                    $return_uri = $instance->getParameter('returnURI', $request_uri);
                    $redirect = implode("/", array_map("rawurlencode", explode("/", $return_uri)));
                    $this->debug($redirect);
                    $this->response()->redirect($auth->_loginAdminPage . '&returnURI=' . $redirect);
                    return true;
                } else if (!$auth->checkAuth($instance)) {
                    $request_uri = if_exists($_SERVER, 'X_HTTP_ORIGINAL_URL', $_SERVER ['REQUEST_URI']);
                    $return_uri = $instance->getParameter('returnURI', $request_uri);
                    $redirect = implode("/", array_map("rawurlencode", explode("/", $return_uri)));
                    $this->debug($redirect);
                    $this->response()->redirect($auth->_loginPage . '&returnURI=' . $redirect);
                    return true;
                }

                //TODO::
                try {
                    $this->info(sprintf('The Class "%s" does "%s" method', $controller, $action));
                    $result = call_user_func_array(array($instance, $action), $parameters);
                    $this->debug($result);
                } catch (AjaxException $ex) {
                    $this->err($ex->getMessage());
                    $this->response()->setContent($ex->getMessage());
                    return false;
                } catch (Exception $ex) {
                    $this->err($ex->getMessage());
                    return false;
                }

                //TODO::
                if ($this->ajax() && $this->method() == 'POST') {
                    $responseContents = [];
                    if (is_array($result)) {
                        $responseContents += $result;
                    } elseif (is_string($result)) {
                        $responseContents ['result'] = $result;
                    }
                    $this->debug($responseContents);
                    $this->response()->setContent($responseContents);
                    return true;
                }

                return ($result) ?: true;
        }
        return false;
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
}