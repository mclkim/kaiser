<?php
declare(strict_types=1);

namespace Mcl\Kaiser\Middleware;

use Mcl\Kaiser\ControllerInterface;
use Mcl\Kaiser\Router;

class RoutingMiddleware //implements Middleware
{
    private $container;
    private $map;

    function __construct(
        $container = null,
        $map = null)
    {
        $this->container = $container;
        $this->map = $map;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke()
    {
        $router = new Router($this->map);
        $route = $router->dispatch();

        if (is_array($route) && $route[0] === Router::FOUND) {
            $callable = new $route[1] ($this->container);
            if ($callable instanceof ControllerInterface) {
                return [
                    'methods' => $callable->methods(),
                    'pattern' => $router->getPath(),
                    'callable' => $callable,
                    'handler' => [$callable, $route[2]]
                ];
            }
        }
        return false;
    }
}
