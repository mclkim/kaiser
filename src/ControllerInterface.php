<?php
/**
 * Created by PhpStorm.
 * User: 김명철
 * Date: 2018-04-22
 * Time: 오전 8:06
 */

namespace Kaiser;


interface ControllerInterface
{
    function requireLogin();

    function requireAdmin();

    function execute();
}