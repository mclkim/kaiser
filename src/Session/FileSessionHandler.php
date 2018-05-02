<?php
/**
 * Created by PhpStorm.
 * User: 김명철
 * Date: 2018-05-02
 * Time: 오후 1:37
 */

namespace Kaiser\Session;

class FileSessionHandler
{
    function __construct()
    {
        ini_set('session.save_handler', 'files');
        
        session_set_save_handler(new \PHPSecureSession\SecureHandler(), true);
    }

    function start_session($sessionName = 'PHPSESSID')
    {
        // change the default session folder in a temporary dir
        session_save_path(sys_get_temp_dir());

        // Change the session name
        session_name($sessionName);

        session_start();
    }
}