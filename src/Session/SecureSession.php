<?php

namespace Kaiser\Session;

class SecureSession extends \Kaiser\Session
{
    protected function register_session()
    {
        ini_set('session.save_handler', 'files');

        // Register the secure session handler
        session_set_save_handler(new \PHPSecureSession\SecureHandler(), true);

        // This line prevents unexpected effects when using objects as save handlers.
        register_shutdown_function('session_write_close');
    }
}