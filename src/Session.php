<?php

namespace Kaiser;

class Session
{
    protected function register_session()
    {
        ini_set('session.save_handler', 'files');

        // This line prevents unexpected effects when using objects as save handlers.
        register_shutdown_function('session_write_close');
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