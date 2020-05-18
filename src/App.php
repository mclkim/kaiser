<?php
declare(strict_types=1);

/**
 * @link      https://github.com/mclkim/kaiser
 * @copyright Copyright (p) myung-chul kim
 * @license   MIT License
 */

namespace Mcl\Kaiser;

class App extends \Slim\App
{
    const DATE_APPROVED = '2019.12.15';
    const VERSION = '20191215';

    function run($appMap = ['App\\' => 'app']): void
    {
        $router = new Router();
        $router->setAppMap($appMap);
        $routeInfo = $router->dispatch();
        if (is_array($routeInfo) && $routeInfo[0] == Router::FOUND) {
            $callable = new $routeInfo[1] ($this->container);
            if ($callable instanceof ControllerInterface) {
                $this->map($callable->methods(), $router->getPath(), [$callable, $routeInfo[2]])->add(new Auth($callable));
            }
        }

        parent::run();
    }
}