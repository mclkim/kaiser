<?php
/**
 * Created by PhpStorm.
 * User: 김명철
 * Date: 2018-05-02
 * Time: 오후 1:37
 */

namespace Kaiser\Session;


use Kaiser\Manager\DBManager;
use Kaiser\Timestamp;
use Kaiser\Timer;

class DbSessionHandler extends \PHPSecureSession\SecureHandler
{
    const SESS_EXPIRATION = 7200; // the number of SECONDS you want the session to last.

    protected $db;

    function __construct($pdo = null)
    {
        parent::__construct();

        $this->db = new DBManager($pdo);

        session_cache_expire($this->session_cache_expire_minutes); #2 hours

        $cache_expire = session_cache_expire();

        session_cache_limiter('private');

        $cache_limiter = session_cache_limiter();

        session_set_save_handler(
            array(&$this, "open"), array(&$this, "close"), array(&$this, "read"), array(&$this, "write"), array(&$this, "destroy"), array(&$this, "gc")
        );

        if (!is_null($this->session_name)) {
            session_name($this->session_name);
        }

        if ($this->session_auto_start) {
            session_start();
        }
    }

    public function __destruct()
    {
        session_write_close();
    }

    public function open($save_path, $session_name)
    {
        $this->key = $this->getKey('KEY_' . $session_name);
        if (is_null($this->db)) return false;
        return true;
    }

    function close()
    {
        $this->db = null;
        return true;
    }

    public function read($id)
    {
        $sql = "SELECT privilege FROM sessions WHERE id = ?";

        $data = $this->db->executePreparedQueryOne($sql, array(
            $id
        ));

        return empty($data) ? '' : $this->decrypt($data, $this->key);
    }

    public function write($id, $data)
    {
        $encrypt = $this->decrypt($data, $this->key);

        $data = array(
            'id' => $id,
            'privilege' => $encrypt,
            'updated' => Timestamp::getUNIXtime()
        );

        return $res = $this->db->AutoExecuteReplace('sessions', $data);
    }

    function destroy($session_id)
    {
    }

    function gc($maxlifetime)
    {
    }
}