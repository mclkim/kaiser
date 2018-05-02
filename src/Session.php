<?php

namespace Kaiser;

use SessionHandler;

class Session
{
    function __construct()
    {
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