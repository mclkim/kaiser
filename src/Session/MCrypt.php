<?php
/**
 * Created by PhpStorm.
 * User: losys99
 * Date: 2016-08-18
 * Time: 오전 10:18
 */

namespace Kaiser\Session;

class MCrypt
{
    private $key = 'd0a7e7997b6d5fcd55f4b5c32611b87cd923e88837b63bf2941ef819dc8ca282';

    function __construct($key)
    {
        if (!empty($key)) {
            $this->setKey($key);
        }
    }

    public function encrypt($encrypt)
    {
        $encrypt = serialize($encrypt);
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC), MCRYPT_DEV_URANDOM);
        $key = pack('H*', $this->key);
        $mac = hash_hmac('sha256', $encrypt, substr($this->key, -32));
        $passcrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $encrypt . $mac, MCRYPT_MODE_CBC, $iv);
        $encoded = base64_encode($passcrypt) . '|' . base64_encode($iv);
        return $encoded;
    }

    public function decrypt($decrypt)
    {
        $decrypt = explode('|', $decrypt . '|');
        $decoded = base64_decode($decrypt[0]);
        $iv = base64_decode($decrypt[1]);
        if (strlen($iv) !== mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC)) {
            return false;
        }
        $key = pack('H*', $this->key);
        $decrypted = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $decoded, MCRYPT_MODE_CBC, $iv));
        $mac = substr($decrypted, -64);
        $decrypted = substr($decrypted, 0, -64);
        $calcmac = hash_hmac('sha256', $decrypted, substr($this->key, -32));
        if ($calcmac !== $mac) {
            return false;
        }
        $decrypted = unserialize($decrypted);
        return $decrypted;
    }

// Define a 32-byte (64 character) hexadecimal encryption key
// Note: The same encryption key used to encrypt the data must be used to decrypt the data
    public function setKey($key)
    {
        if (ctype_xdigit($key) && strlen($key) === 64) {
            $this->key = $key;
        } else {
            trigger_error('Invalid key. Key must be a 32-byte (64 character) hexadecimal string.', E_USER_ERROR);
        }
    }
}