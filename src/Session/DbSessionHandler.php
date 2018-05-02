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

class DbSessionHandler extends SecureHandler
{
    const SESS_EXPIRATION = 7200; // the number of SECONDS you want the session to last.

    protected $db;

    function __construct($pdo = null)
    {
        parent::__construct();

        $this->db = new DBManager($pdo);

        // set our custom session functions.
        session_set_save_handler(array($this, "open"), array($this, "close"), array($this, "read"), array($this, "write"), array($this, "destroy"), array($this, "gc"));
    }

    function start_session($sessionName = 'PHPSESSID')
    {
        // change the default session folder in a temporary dir
        session_save_path(sys_get_temp_dir());

        // Change the session name
        session_name($sessionName);

        session_start();
    }

    public function __destruct()
    {
        session_write_close();
    }

    public function open($save_path, $session_name)
    {
        return true;
    }

    function close()
    {
        $this->db = null;
        return true;
    }

    public function read($id)
    {
        $sql = "SELECT privilege,session_key FROM sessions WHERE id = ?";

        $data = $this->db->executePreparedQueryToMap($sql, array(
            $id
        ));

        $key = $data['session_key'];
        return empty($data['privilege']) ? '' : $this->encrypt(base64_decode($data['privilege']), $key);
    }

    public function write($id, $data)
    {
        $key = $this->session_key($id);
        $encrypt = $this->encrypt($data, $key);

        logger()->error($key);
        logger()->error($data);
        logger()->error($encrypt);

        $data = array(
            'id' => $id,
            'privilege' => base64_encode($encrypt),
            'session_key' => $key,
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

    protected function session_key($session_id)
    {
        $sql = "SELECT session_key FROM sessions WHERE id = ? LIMIT 1";

        $res = $this->db->executePreparedQueryOne($sql, array(
            $session_id
        ));

//        return ($res) ? $res : substr(hash('sha512', uniqid(mt_rand(1, 32), true)), 0, 64);
        return ($res) ? $res : base64_encode(random_bytes(64));
    }
}