<?php
/**
 * Created by PhpStorm.
 * User: 김명철
 * Date: 2018-04-22
 * Time: 오전 8:05
 */

namespace Kaiser\Manager;


interface DBManagerInterface
{
    function getList();

    function getMapList();

    function getData();

    function putData();

    function delData();
}