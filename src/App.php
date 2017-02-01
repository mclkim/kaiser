<?php

namespace Kaiser;

use Kaiser\Exception\ApplicationException;
use Kaiser\Exception\AjaxException;

class App extends Controller
{
    const VERSION = '2016-12-26';
    var $timestamp = null;
    static $AppDirectory;
    static $basePath;

    function __construct($container = [])
    {
        parent::__construct($container);
        /**
         * 시작을 로그파일에 기록한다.
         */
//        $this->info(sprintf('The Class "%s" Initialized ', get_class($this)));
        /**
         * 타임스템프를 기록..
         */
//        $this->timestamp = new \Kaiser\Timer ();
    }

    function __destruct()
    {
        /**
         * 타임스템프를 기록한 시간 차이를 계산하여 기록한다.
         * 사용한 메모리를 기록한다.
         */
//        $this->info(sprintf('The Class "%s" total execution time: ', get_class($this)) . $this->timestamp->fetch() . ", Memory used: " . bytesize(memory_get_peak_usage()));
    }

    public function start()
    {
        /**
         * 시작을 로그파일에 기록한다.
         */
        $this->info(sprintf('The Class "%s" Initialized ', get_class($this)));
        /**
         * 타임스템프를 기록..
         */
        $this->timestamp = new \Kaiser\Timer ();

        //$this->debug('start');
    }

    public function end()
    {
        //$this->debug('end');
        /**
         * 타임스템프를 기록한 시간 차이를 계산하여 기록한다.
         * 사용한 메모리를 기록한다.
         */
        $this->info(sprintf('The Class "%s" total execution time: ', get_class($this)) . $this->timestamp->fetch() . ", Memory used: " . bytesize(memory_get_peak_usage()));
    }

    public function version()
    {
        return self::VERSION;
    }

    protected function getAjaxHandler()
    {
        $request = $this->request();

        if (!$this->ajax() || $this->method() != 'POST') {
            return null;
        }

        if ($handler = $request->header('X-October-Request-Handler')) {
            return trim($handler);
        }

        if ($handler = $request->header('X-Request-Handler')) {
            return trim($handler);
        }

        return null;
    }

    protected function getPostHandler()
    {
        $request = $this->request();

        if ($this->method() != 'POST') {
            return null;
        }

        if ($handler = $request->post('X-October-Request-Handler')) {
            return trim($handler);
        }

        if ($handler = $request->header('X-Request-Handler')) {
            return trim($handler);
        }

        return null;
    }

    /**
     * Execute the controller action.
     */
    public function run()
    {
        // phpinfo();exit;
        //$this->debug('------------------');
        //$this->debug($_SERVER);
        //$this->debug($_POST);
        //$this->debug($_REQUEST);
        //$this->debug($_FILES);
        //$this->debug('------------------');

        $this->start();

        /**
         * TODO::
         * 세션스타트..
         */
        $this->container->get('session');

        if (!$this->getCsrfToken()) {
            $this->setCsrfToken();
        }

        /**
         * Check security token.
         */
        if (!$this->verifyCsrfToken()) {
            throw new ApplicationException ('Invalid security token.');
        }
        //$this->debug('hello #1');
        /**
         * Execute AJAX event
         */
        if (($ajaxResponse = $this->execAjaxHandler()) != null) {
            //$this->debug('hello #2');
            //$this->debug($ajaxResponse);
            return $ajaxResponse;
        }
        //$this->debug('hello #3');
        /**
         * Execute page action
         */
        $result = $this->execPageAction();
        //$this->debug('hello #4');
        //$this->debug($result);
        if (is_string($result)) {
            return $result;
        }
        //$this->debug('hello #5');

        $this->end();
    }

    protected
    function execAjaxHandler()
    {
        if (($handler = $this->getAjaxHandler()) == null) {
            //$this->debug('hello #00');
            return null;
        }

        try {
            //$this->debug('hello #11');
            $rout = $this->router();
            $rout->setQuery($handler);

            /**
             * URL의 라우팅 설정
             * 실행파일의 클래스명과 실행메소드를 분리하여 구한다.
             */
            $router = $rout->getRoute();

            /**
             * 클래스명과 파일 경로를 전달받아 클래스 인스턴스를 생성한다.
             */
            $callable = $this->findController($router->controller, $router->action, $this->getAppDir());

            /**
             * 클래스 인스턴스를 실행한다.
             */
            if (!$result = $this->runAjaxHandler($callable)) {
//                $this->err($result);
//                return $result;
                $this->err(sprintf('The Class "%s" does "%s" method', get_class($callable [0]), $callable [1]));
                throw new ApplicationException ('runAjaxHandler');
            }

            //$this->debug($result);
            //$this->debug('hello #12');
            $responseContents = [];

            /**
             * 핸들러가 배열을 반환 한 경우 렌더링을 위해 출력에 추가해야 합니다.
             * 문자열 인 경우 키 "result"를 사용하여 배열에 추가합니다.
             */
            if (is_array($result)) {
                $responseContents = array_merge($responseContents, $result);
            } elseif (is_string($result)) {
                $responseContents ['result'] = $result;
            }

            //$this->debug('hello #13');
            $this->response()->setContent($responseContents);
            //$this->debug($result);
            return ($result) ?: true;
//        } catch (AjaxException $ex) {
//            $this->err($ex->getMessage());
//            $this->response()->setContent($ex->getMessage());
//        } catch (ApplicationException $ex) {
//            $this->err($ex->getMessage());
        } catch (Exception $ex) {
            $this->err($ex->getMessage());
            throw $ex;
        }

        return -1;
    }

    private
    function runAjaxHandler($callable)
    {
        try {
            $result = null;

//            if (!method_exists($callable [0], $callable [1])) {
//                $this->err($callable);
//                $this->err(sprintf('Action "%s" is not found in the controller "%s"', $callable [1], $callable [0]));
//                throw new SystemException (sprintf("Action %s is not found in the controller %s", $callable [1], $callable [0]));
//            }

            /**
             * Not logged in, redirect to login screen or show ajax error.
             * 로그인 여부를 체크 할 페이지 인지 확인한다.
             * TODO::다른 좋은 방법이 있을 것 같은데~
             */
            $request_query = $this->router()->getQueryString();
//            $request_uri = if_exists($_SERVER, 'X_HTTP_ORIGINAL_URL', $_SERVER ['REQUEST_URI']);
            $request_uri = if_exists($_SERVER, 'X_HTTP_ORIGINAL_URL', $request_query);
            $return_uri = $callable [0]->getParameter('returnURI', $request_uri);
            $redirect = '?' . implode("/", array_map("rawurlencode", explode("/", $return_uri)));
//            logger($request_query);
//            logger($request_uri);
//            logger($return_uri);
//            logger($redirect);

            if (!$this->checkAdmin($callable [0])) {
                if ($this->ajax()) {
                    return 'Access denied!';
                } else {
                    $this->response()->redirect($this->_loginAdminPage . '&returnURI=' . $redirect);
                    return true;
                }
            } else if (!$this->check($callable [0])) {
                if ($this->ajax()) {
                    return 'Access denied!';
                } else {
                    $this->response()->redirect($this->_loginPage . '&returnURI=' . $redirect);
                    return true;
                }
            }

            /**
             * Execute the handler
             */
            $this->info(sprintf('The Class "%s" does "%s" method', get_class($callable [0]), $callable [1]));
            $result = call_user_func_array($callable, []);
            //$this->debug($result);
            return ($result) ?: true;
        } catch (AjaxException $ex) {
            $this->err($ex->getMessage());
            $this->response()->setContent($ex->getMessage());
        } catch (Exception $ex) {
            $this->err($ex->getMessage());
            throw $ex;
        }
        //$this->debug($result);
        return $result;
    }

    protected
    function execPageAction()
    {
        try {
            $rout = $this->router();

            if ($handler = $this->getPostHandler()) {
                //$this->debug('hello #10');
                $rout->setQuery($handler);
            }

            /**
             * URL의 라우팅 설정
             * 실행파일의 클래스명과 실행메소드를 분리하여 구한다.
             */
            $router = $rout->getRoute();

            /**
             * 클래스명과 파일 경로를 전달받아 클래스 인스턴스를 생성한다.
             */
            $callable = $this->findController($router->controller, $router->action, $this->getAppDir());

            /**
             * 클래스 인스턴스를 실행한다.
             */
            if (!$result = $this->runAjaxHandler($callable)) {
//                $this->err($result);
//                return $result;
                $this->err(sprintf('The Class "%s" does "%s" method', get_class($callable [0]), $callable [1]));
                throw new ApplicationException ('runAjaxHandler');
            }

            return ($result) ?: true;
//        } catch (AjaxException $ex) {
//            $this->err($ex->getMessage());
//            $this->response()->setContent($ex->getMessage());
//        } catch (ApplicationException $ex) {
//            $this->err($ex->getMessage());
        } catch (Exception $ex) {
            $this->err($ex->getMessage());
            throw $ex;
        }

        return -1;
    }

    public static function normalizeClassName($name)
    {
        $name = str_replace('/', '\\', $name);

        if (is_object($name))
            $name = get_class($name);

        $name = '\\' . ltrim($name, '\\');
        return $name;
    }

    protected function findController($controller, $action, $inPath)
    {
        $directory = is_array($inPath) ? $inPath : array(
            $inPath
        );

        /**
         * Workaround: Composer does not support case insensitivity.
         * TODO::2016-12-02 unix 시스템에서 파일이름의 대소문자 구별한다.
         */
        if (!class_exists($controller)) {
            $controller = self::normalizeClassName($controller);
            foreach ($directory as $inPath) {
//                $controllerFile = $inPath . strtolower(str_replace('\\', '/', $controller)) . '.php';
                $controllerFile = $inPath . str_replace('\\', '/', $controller) . '.php';
                if (file_exists($controllerFile)) {
                    include_once($controllerFile);
                    break;
                }
            }
        }

        if (!class_exists($controller)) {
//          return false;
            throw new ApplicationException (sprintf('The Class "%s" does not found', $controller));
        }

        $controllerObj = [
            new $controller ($this->container),
            $action
        ];

        if (is_callable($controllerObj)) {
            return $controllerObj;
        }

//      return false;
        throw new ApplicationException (sprintf("The Action '%s' is not found in the controller '%s'", $action, $controller));
    }

    function setAppDir($directory = [])
    {
        self::$AppDirectory = $directory;
    }

    function getAppDir()
    {
        return self::$AppDirectory;
    }

    function setBasePath($directory)
    {
        self::$basePath = $directory;
    }

    function getBasePath()
    {
        return self::$basePath;
    }
}
