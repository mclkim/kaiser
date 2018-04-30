<?php

namespace Kaiser;

class Session extends \PHPSecureSession\SecureHandler
{
    public function __construct()
    {
        parent::__construct();
        $this->register_session();
    }

    protected function register_session()
    {
        ini_set('session.save_handler', 'files');

        // set our custom session functions.
        session_set_save_handler(array($this, "open"), array($this, "close"), array($this, "read"), array($this, "write"), array($this, "destroy"), array($this, "gc"));

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