<?php
/**
 * http://www.wikihow.com/Create-a-Secure-Login-Script-in-PHP-and-MySQL
 * http://www.wikihow.com/Create-a-Secure-Session-Management-System-in-PHP-and-MySQL
 * ---------------------------------------------------------------
 * CREATE DATABASE `sessionsDB` ;
 * CREATE USER 'sec_user'@'localhost' IDENTIFIED BY 'eKcGZr59zAa2BEWU';
 * GRANT SELECT, INSERT, UPDATE, DELETE ON `sessionsDB`.* TO 'sec_user'@'localhost';
 * flush privileges;
 * ---------------------------------------------------------------
 * USE `sessionsDB`;
 * DROP TABLE IF EXISTS `sessions`;
 * CREATE TABLE `sessions` (
 * `no` int(11) unsigned NOT NULL AUTO_INCREMENT,
 * `id` varchar(255) NOT NULL DEFAULT '' COMMENT '세션ID',
 * `address` varchar(15) NOT NULL DEFAULT '' COMMENT '세션접속IP',
 * `agent` varchar(512) NOT NULL DEFAULT '' COMMENT '세션접속Agent',
 * `userid` varchar(64) DEFAULT NULL COMMENT '사용자ID',
 * `preexistence` int(11) DEFAULT NULL COMMENT '존재여부',
 * `regtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '입력시각',
 * `privilege` text COMMENT '세션정보',
 * `server` varchar(64) NOT NULL DEFAULT '' COMMENT '접속서버정보',
 * `request` varchar(255) DEFAULT NULL COMMENT '요청정보',
 * `referer` varchar(255) DEFAULT NULL COMMENT '참조정보',
 * `timer` float NOT NULL DEFAULT '0' COMMENT '타이머',
 * `created` int(11) NOT NULL DEFAULT '0' COMMENT '생성시간',
 * `updated` int(11) NOT NULL DEFAULT '0' COMMENT '수정시간',
 * `session_key` text NOT NULL COMMENT '세션KEY',
 * PRIMARY KEY (`no`),
 * UNIQUE KEY `idx_sessions_id` (`id`) ,
 * KEY `updated` (`updated`)
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8
 */
namespace Kaiser\Session;

use Kaiser\Manager\DBManager;
use Kaiser\Timestamp;
use Kaiser\Timer;

final class DBSession extends DBManager
{
    var $enableLogging = false;
//    var $key = 'nh9a6d2b6s6g9ynh';// but size 16 is required
//    var $iv = 'ddky2235gee1g3mr';// but size 16 is required
    private static $sessionMicrotime;
    private static $sess_expiration = 7200; // the number of SECONDS you want the session to last.

    function __construct($connection)
    {
        parent::__construct($connection);

        self::$sessionMicrotime = Timer::getMicroTime();

        // set our custom session functions.
        session_set_save_handler(array($this, "open"), array($this, "close"), array($this, "read"), array($this, "write"), array($this, "destroy"), array($this, "gc"));

        // This line prevents unexpected effects when using objects as save handlers.
        register_shutdown_function('session_write_close');

        // $this->debug ( sprintf ( 'The Class "%s" Initialized ', get_class ( $this ) ) );
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
        // 많은 해시의 문자 비트.
        // The possible values are '4' (0-9, a-f), '5' (0-9, a-v), and '6' (0-9, a-z, A-Z, "-", ",").
        ini_set('session.hash_bits_per_character', 5);
        // 쿠키 만이 아닌 URL 변수를 사용하여 세션을 강제로.
        ini_set('session.use_only_cookies', 1);
        // 세션 쿠키의 매개 변수를 가져옴
        $cookieParams = session_get_cookie_params();
        // 매개 변수를 설정합니다
        session_set_cookie_params($cookieParams ["lifetime"], $cookieParams ["path"], $cookieParams ["domain"], $secure, $httponly);
        // 세션을 시작
        session_name($sessionName);
        // Now we cat start the session
        session_start();
        /**
         * TODO::
         * 이 줄은 세션을 다시 생성하고 기존 하나를 삭제합니다.
         * 또한 데이터베이스에 새로운 암호화 키를 생성한다.
         */
        // session_regenerate_id ( true );
    }

    function open($savePath, $sessionName)
    {
        return true;
    }

    function close()
    {
        return true;
    }

    function read($sessionId)
    {
        $sql = "SELECT privilege FROM sessions 
				WHERE id = ? AND updated >= ?";

        $data = $this->executePreparedQueryOne($sql, array(
            $sessionId,
            Timestamp::getUNIXtime() - self::$sess_expiration
        ));

        $key = $this->getkey($sessionId);

        // TODO::sudo php5enmod mcrypt
        // $crypt = new Crypt ();
        // $crypt->setComplexTypes ( TRUE );
        // $crypt->setKey ( $key );
        // $crypt->setData ( $data );
        // $decrypted = $crypt->decrypt ();

//        $crypt = new \Crypt\AES ();
//        $decrypt = $crypt->decrypt($data, $this->key, $this->iv);
//        $security = new AES();
//        $decrypt = $security->decrypt($data, $this->key, $this->iv);

//        $security = new Security();
//        $data = base64_decode($data);//TODO::특수값이 처리를 위해서
//        $decrypt = $security->decrypt($data, $key);

        $crypt = new Crypt();
        $decrypt = $crypt->decrypt($data, $key);

//        $this->err($data);
//        $this->err($decrypt);
        return $decrypt;
    }

    function write($sessionId, $data)
    {
        $meet_again_baby = 900;

        $key = $this->getkey($sessionId);

        // TODO::sudo php5enmod mcrypt
        // $crypt = new Crypt ();
        // $crypt->setComplexTypes ( TRUE );
        // $crypt->setKey ( $key );
        // $crypt->setData ( $data );
        // $encrypted = $crypt->encrypt ();

//        $crypt = new \Crypt\AES ();
//        $encrypt = $crypt->encrypt($data, $this->key, $this->iv);
//        $security = new AES();
//        $encrypt = $security->encrypt($data, $this->key, $this->iv);

//        $security = new Security();
//        $encrypt = $security->encrypt($data, $key);

        $crypt = new Crypt();
        $encrypt = $crypt->encrypt($data, $key);

//        $this->err($data);
//        $this->err($encrypt);
        $userid = if_empty($_SESSION, 'userid', null);

        $data = array(
            'id' => $sessionId,
            'address' => $_SERVER ['REMOTE_ADDR'],
            'agent' => $_SERVER ['HTTP_USER_AGENT'],
            'userid' => $userid,
//            'privilege' => base64_encode($encrypt),
            'privilege' => $encrypt,
            'server' => $_SERVER ['HTTP_HOST'],
            'request' => substr($_SERVER ['REQUEST_URI'], 0, 255),
            'referer' => isset ($_SERVER ['HTTP_REFERER']) ? substr($_SERVER ['HTTP_REFERER'], 0, 255) : '',
            'timer' => Timer::getMicroTime() - self::$sessionMicrotime,
            'created' => Timestamp::getUNIXtime(),
            'updated' => Timestamp::getUNIXtime() - $meet_again_baby,
            'session_key' => $key
        );

        $res = $this->AutoExecuteReplace('sessions', $data);
        return $res;
    }

    function destroy($sessionId)
    {
        $sql = "DELETE FROM sessions WHERE id = ?";

        $res = $this->executePreparedUpdate($sql, array(
            $sessionId
        ));

        return $res;
    }

    /**
     * Garbage Collector
     */
    function gc($maxLifeTime = false)
    {
        $sql = "DELETE FROM sessions WHERE updated < ?";

        $res = $this->executePreparedUpdate($sql, array(
            Timestamp::getUNIXtime() - $maxLifeTime
        ));

        return $res;
    }

    private function getkey($sessionId)
    {
        $sql = "SELECT session_key FROM sessions WHERE id = ? LIMIT 1";

        $res = $this->executePreparedQueryOne($sql, array(
            $sessionId
        ));

        if ($res) {
            return $res;
        } else {
            // return hash ( 'sha512', uniqid ( mt_rand ( 1, mt_getrandmax () ), true ) );
            return hash('sha512', uniqid(mt_rand(1, 32), true));
        }
    }
}
