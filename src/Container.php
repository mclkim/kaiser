<?php
/**
 * Created by PhpStorm.
 * User: 김명철
 * Date: 2018-04-23
 * Time: 오후 1:46
 */

namespace Kaiser;

use Pimple\Container as PimpleContainer;
use Psr\Container\ContainerInterface;

final class Container extends PimpleContainer implements ContainerInterface
{
    function get($id)
    {
        if (!$this->offsetExists($id)) {
            throw new \RuntimeException (sprintf('Identifiler "%s" is not defined.', $id));
        }
        return $this->offsetGet($id);
    }

    function has($id)
    {
        return $this->offsetExists($id);
    }
}