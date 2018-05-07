<?php
/**
 * Created by PhpStorm.
 * User: 김명철
 * Date: 2018-05-07
 * Time: 오후 10:58
 */

namespace Kaiser\Session;

// use SessionHandlerInterface;
use Kaiser\Manager\DBManager;
use Kaiser\Timestamp;
use Kaiser\Timer;

//class DBSessionHandler implements SessionHandlerInterface
class DbSessionHandler extends SecureHandler
{
    protected $db;

    function __construct($pdo = null)
    {
        parent::__construct();

        $this->db = new DBManager($pdo);

        // register_shutdown_function('session_write_close');
    }

    public function open($save_path, $session_name)
    {
        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($id)
    {
//        global $cid;
//        // Set empty result
//        $data = '';
//        // Fetch session data from the selected database
//        $sql = $cid->prepare("SELECT session_data FROM sessions WHERE session_id = ?");
//        $sql->bind_param("s", $id);
//        $sql->execute() or trigger_error("Session Error: " . mysqli_error($cid) . " -- Query: " . $sql);
//        $rs = $sql->get_result();
//
//        $a = mysqli_num_rows($rs);
//        if ($a > 0) {
//            $row = mysqli_fetch_assoc($rs);
//            $data = $row['session_data'];
//        }
//        #trigger_error("read - " . $_SERVER['REQUEST_URI']);
//        settype($data, "string");
        $sql = "SELECT privilege,session_key FROM sessions WHERE id = ?";

        $row = $this->db->executePreparedQueryToMap($sql, array(
            $id
        ));

        if (!is_null($row['privilege'])) {
//            $data = $row['privilege'];
            $key = $row['session_key'];
            $privilege = base64_decode($row['privilege']);
            $data = $this->decrypt($privilege, $key);
        }
        settype($data, "string");
        return $data;
    }

    public function write($id, $data)
    {
        $time = time() + get_cfg_var("session.gc_maxlifetime");
     
        $key = $this->session_key($id);
        $privilege = $this->encrypt($data, $key);

//        $sql = $cid->prepare("REPLACE INTO sessions (session_id,session_data,expires) VALUES (?, ?, ?)");
//        $sql->bind_param("ssi", $id, $data, $time);
//        $sql->execute() or trigger_error("Session Error: " . mysqli_error($cid) . " -- Query: " . $sql);

        #trigger_error("write - " . $_SERVER['REQUEST_URI']. " - " . mysqli_thread_id($cid));
        $data = array(
            'id' => $id,
            'privilege' => base64_encode($privilege),
           'session_key' => $key,
           'updated' => Timestamp::getUNIXtime()
        );
//        return TRUE;
        return $res = $this->db->AutoExecuteReplace('sessions', $data);
    }

    public function destroy($id)
    {
//        global $cid;
//
//        $sql = $cid->prepare("DELETE FROM sessions WHERE session_id = ?");
//        $sql->bind_param("s", $id);
//        $sql->execute() or trigger_error("Session Error: " . mysqli_error($cid) . " -- Query: " . $sql);

        return TRUE;
    }

    public function gc($maxlifetime)
    {
        // Garbage Collection
//        global $cid;
//
//        $sql = 'DELETE FROM sessions WHERE expires < UNIX_TIMESTAMP();';
//        mysqli_query($cid, $sql) or trigger_error("Session Error: " . mysqli_error($cid) . " -- Query: " . $sql);
//         Always return TRUE

        return true;
    }

    protected function session_key($session_id)
    {
        $sql = "SELECT session_key FROM sessions WHERE id = ?";

        $res = $this->db->executePreparedQueryOne($sql, array(
            $session_id
        ));

//        return ($res) ? $res : substr(hash('sha512', uniqid(mt_rand(1, 32), true)), 0, 64);
        return ($res) ? $res : base64_encode(random_bytes(64));
    }
}

//$handler = new DBSessionHandler();
//session_set_save_handler($handler, true);
//
//session_start();