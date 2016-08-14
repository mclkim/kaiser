<?php

namespace Kaiser\Session;

final class FileSession
{
//    var $key = 'nh9a6d2b6s6g9ynh';
//    var $iv = 'ddky2235gee1g3mr';
    private $savePath;

    public function __construct($savePath = null)
    {
        // $temp_file = tempnam(sys_get_temp_dir(), 'Tux');
        if (isset($savePath))
            $this->savePath = $savePath;
        else
            $this->savePath = session_save_path();//sys_get_temp_dir();

//var_dump($this->savePath);exit;

        // set our custom session functions.
        session_set_save_handler(array($this, "open"), array($this, "close"), array($this, "read"), array($this, "write"), array($this, "destroy"), array($this, "gc"));

        // This line prevents unexpected effects when using objects as save handlers.
        register_shutdown_function('session_write_close');
    }

    function __destruct()
    {
    }

    function start_session($sessionName = 'PHPSESSID', $secure = false)
    {
        // Make sure the session cookie is not accessable via javascript.
        $httponly = true;
        // Hash algorithm to use for the sessionid. (use hash_algos() to get a list of available hashes.)
        $session_hash = 'sha512';
        // Check if hash is available
        if (in_array($session_hash, hash_algos())) {
            // Set the has function.
            ini_set('session.hash_function', $session_hash);
        }
        // How many bits per character of the hash.
        // The possible values are '4' (0-9, a-f), '5' (0-9, a-v), and '6' (0-9, a-z, A-Z, "-", ",").
        ini_set('session.hash_bits_per_character', 5);
        // Force the session to only use cookies, not URL variables.
        ini_set('session.use_only_cookies', 1);
        // Get session cookie parameters
        $cookieParams = session_get_cookie_params();
        // Set the parameters
        session_set_cookie_params($cookieParams ["lifetime"], $cookieParams ["path"], $cookieParams ["domain"], $secure, $httponly);
        // Change the session name
        session_name($sessionName);
        // Now we cat start the session
        session_start();
    }

    function open($savePath, $sessionName)
    {
        // $this->savePath = $savePath;
        if (!is_dir($this->savePath)) {
            mkdir($this->savePath, 0777);
        }
        return true;
    }

    function close()
    {
        return true;
    }

    function read($sessionId)
    {
        $data = ( string )@file_get_contents("$this->savePath/sess_$sessionId");
        $key = $this->getkey($sessionId);

        // TODO::sudo php5enmod mcrypt
        // $crypt = new Crypt ();
        // $crypt->setComplexTypes ( TRUE );
        // $crypt->setKey ( $key );
        // $crypt->setData ( $data );
        // $decrypted = $crypt->decrypt ();

//        $crypt = new \Crypt\AES ();
//        $decrypt = $crypt->decrypt($data, $this->key, $this->iv);

        $security = new Security();
        $decrypt = $security->decrypt($data);

        return $decrypt;
    }

    function write($sessionId, $data)
    {
        // Get unique key
        $key = $this->getkey($sessionId);

        // TODO::sudo php5enmod mcrypt
        // $crypt = new Crypt ();
        // $crypt->setComplexTypes ( TRUE );
        // $crypt->setKey ( $key );
        // $crypt->setData ( $data );
        // $encrypted = $crypt->encrypt ();

//        $crypt = new \Crypt\AES ();
//        $encrypt = $crypt->encrypt($data, $this->key, $this->iv);

        $security = new Security();
        $encrypt = $security->encrypt($data);

        return file_put_contents("$this->savePath/sess_$sessionId", $encrypt) === false ? false : true;
    }

    function destroy($sessionId)
    {
        $file = "$this->savePath/sess_$sessionId";
        if (file_exists($file)) {
            unlink($file);
        }

        return true;
    }

    /**
     * Garbage Collector
     */
    function gc($maxLifeTime = false)
    {
        foreach (glob("$this->savePath/sess_*") as $file) {
            if (filemtime($file) + $maxLifeTime < time() && file_exists($file)) {
                unlink($file);
            }
        }
        return true;
    }

    private function getkey($sessionId)
    {
        return $sessionId;
    }
}
