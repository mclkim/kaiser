<?php
/**
 * Created by PhpStorm.
 * User: 김명철
 * Date: 2018-04-22
 * Time: 오후 3:34
 */

namespace Mcl\Kaiser;

use Mcl\Kaiser\ApplicationException;
use Mcl\Kaiser\AjaxException;

class App extends Controller
{
    const VERSION = '2018-05-20';
    var $timestamp = null;

    function __construct($container = [])
    {
        $this->setContainer($container);
    }

    public function version()
    {
        return self::VERSION;
    }

    protected function start()
    {
        /**
         * 시작을 로그파일에 기록한다.
         */
        $this->info('<<START --------------------------------------------');
        $this->info(sprintf('The Class "%s" Initialized ', get_class($this)));
        /**
         * 타임스템프를 기록..
         */
        $this->timestamp = new \Mcl\Kaiser\Timer ();
    }

    protected function end()
    {
        /**
         * 타임스템프를 기록한 시간 차이를 계산하여 기록한다.
         * 사용한 메모리를 기록한다.
         */
        $this->info(sprintf('The Class "%s" total execution time: ', get_class($this)) . $this->timestamp->fetch() . ", Memory used: " . bytesize(memory_get_peak_usage()));
        $this->info('---------------------------------------------- END>>');
    }

    public function run($directory = [])
    {
        if ($this->container->has('session'))
            $this->container->get('session');

        $result = $this->execPageAction($directory);
        $this->debug($result);
    }

    protected function execPageAction($directory = [])
    {
        $router = new Router();
        $router->setAppDir($directory);
        $routeInfo = $router->dispatch(array('methods' => ['GET', 'POST']));

        //TODO::
        $controller = $routeInfo[1];
        $action = $routeInfo[2];
        $parameters = $routeInfo[3];

        $this->debug($controller);
        $this->debug($action);
        $this->debug($parameters);

        switch ($routeInfo[0]) {
            case Router::NOT_FOUND:
                // ... 404 Not Found
                $this->err(sprintf('The Class "%s" does not found', $controller));
                $this->status('404', 'Not Found', '1.1');
                break;
            case Router::NOT_FOUND_ACTION:
                // ... 405 Method Not Allowed
                $this->err(sprintf("The Action '%s' is not found in the controller '%s'", $action, $controller));
                $this->status('404', 'Not Found', '1.1');
                break;
            case Router::FOUND:
                //TODO::
                $handler = new $controller;
                $handler->setContainer($this->getContainer());

                /**
                 * TODO::
                 * requireLogin && requireAdmin
                 * Not logged in, redirect to login screen or show ajax error.
                 * 로그인 여부를 체크 할 페이지 인지 확인한다.
                 * TODO::다른 좋은 방법이 있을 것 같은데~
                 */
                $auth = new \Mcl\Kaiser\Auth();
                $request_uri = if_exists($_SERVER, 'X_HTTP_ORIGINAL_URL', $_SERVER ['REQUEST_URI']);
                $return_uri = $handler->getParameter('returnURI', $request_uri);
                $redirect = implode("/", array_map("rawurlencode", explode("/", $return_uri)));
                if (!$auth->checkAdmin($handler)) {
                    $this->debug('redirect=>' . $redirect);
                    $this->response()->redirect($auth->_loginAdminPage . '&returnURI=' . $redirect);
                    return true;
                } else if (!$auth->checkUser($handler)) {
                    $this->debug('redirect=>' . $redirect);
                    $this->response()->redirect($auth->_loginPage . '&returnURI=' . $redirect);
                    return true;
                }

                /**
                 * TODO:
                 * Execute the handler
                 */
                try {
                    $this->info(sprintf('The Class "%s" does "%s" method', $controller, $action));
                    $result = call_user_func_array(array($handler, $action), $parameters);
//                    $this->debug('Execute the handler');
                    $this->debug($result);
                } catch (ApplicationException $ex) {
                    $this->err($ex->getMessage());
                    return false;
                } catch (AjaxException $ex) {
                    $this->err($ex->getMessage());
                    $this->response()->setContent($ex->getMessage());
                    return false;
                } catch (\Exception $ex) {
                    $this->err($ex->getMessage());
                    return false;
                }

                /**
                 * TODO::
                 * Execute AJAX event
                 * 핸들러가 배열을 반환 한 경우 렌더링을 위해 출력에 추가해야 합니다.
                 * 문자열 인 경우 키 "result"를 사용하여 배열에 추가합니다.
                 */
                if ($this->ajax() && $this->method() == 'POST') {
                    $this->info('Execute AJAX event');
                    $responseContents = [];
                    if (is_array($result)) {
                        $responseContents += $result;
                    } elseif (is_string($result)) {
                        $responseContents ['result'] = $result;
                    }
                    $this->debug($responseContents);
                    $this->response()->setContent($responseContents);
                }

                return ($result) ?: true;
        }
        return false;
    }
}