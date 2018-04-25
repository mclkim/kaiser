<?php
/**
 * Created by PhpStorm.
 * User: 김명철
 * Date: 2018-04-22
 * Time: 오후 3:34
 */

namespace Kaiser;

class Appp extends Controller
{
    const VERSION = '2018-04-22';
    var $timestamp = null;

    function __construct($container = [])
    {
        $this->setContainer($container);
    }

    public function run($directory = [])
    {
//        phpinfo();
        $this->start();

        $router = new \Kaiser\Router();
        $router->setAppDir($directory);
        $router->dispatch($this->getContainer());

        $this->end();
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

}