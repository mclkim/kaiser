<?php
/**
 * Created by PhpStorm.
 * User: 김명철
 * Date: 2018-04-22
 * Time: 오후 3:34
 */

namespace Mcl\Kaiser;

class App extends Controller
{
    const VERSION = '2018-07-15';

    public function version()
    {
        return self::VERSION;
    }

    public function run($directory = [])
    {

        if ($this->container->has('session')) {
            $this->container->get('session');
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }
        }

        $request = $this->container->request;
        $response = $this->container->response;

        $path = $request->url(PHP_URL_PATH);

        $router = new Router();
        $router->setAppDir($directory);
        $routeInfo = $router->dispatch($path);

        $this->debug('routeInfo', $routeInfo);

        switch ($routeInfo[0]) {
            case Router::NOT_FOUND:
                // ... 404 Not Found
                // $this->err(sprintf('The Class "%s" does not found', $controller));
                $response->status(404, 'Not Found', '1.1');
                break;
            case Router::NOT_FOUND_ACTION:
                // ... 405 Method Not Allowed
                // $this->err(sprintf("The Action '%s' is not found in the controller '%s'", $action, $controller));
                $response->status(405, 'Method Not Allowed', '1.1');
                break;
            case Router::FOUND:
                //TODO::
                $controller = $routeInfo[1];
                $action = $routeInfo[2];
                $parameters = $routeInfo[3];

                $handler = new $controller($this->container);

                /**
                 * TODO:
                 */
                try {
                    // $this->info(sprintf('The Class "%s" does "%s" method', $controller, $action));
                    // $result = call_user_func_array(array($handler, $action), $parameters);
                    // $this->debug('Execute the handler');
                    // $this->debug($result);
                    $result = call_user_func_array(array($handler, $action), [$request, $response, $parameters]);
                } catch (ApplicationException $ex) {
                    $this->err($ex->getMessage());
                    $result = false;
                } catch (AjaxException $ex) {
                    $this->err($ex->getMessage());
                    $response->setContent($ex->getMessage());
                    $result = false;
                } catch (\Exception $ex) {
                    $this->err($ex->getMessage());
                    $result = false;
                }
                $response->response_sender();
                return ($result) ?: true;
        }
        $response->response_sender();
        return false;
    }
}