<?php
/**
 * Created by PhpStorm.
 * User: losys99
 * Date: 2016-08-14
 * Time: 오후 5:27
 */

namespace Kaiser\Session;

/**
 * http://www.mimul.com/pebble/default/2009/03/13/1236949680000.html
 * php에 구동하기 위해서는 mcrypt를 설치하셔야 합니다.
 *
 * 1. mcrypt 다운로드
 * - http://sourceforge.net/projects/mcrypt
 *
 * 2. mcrypt 설치
 * - ./configure --prefix=/k2/mcrypt;make;make install
 *
 * 3. php에 mycrypt 포함하기
 * - ./configure --with-mcrypt=/k2/mcrypt --disable-posix-threads
 * --enable-dynamic-loading
 *
 * 4. AES 구현 소스
 */
class AES
{
    function __construct()
    {
        $this->key = hex2bin("1ae49a1a1eb120723f07f1260b145526");
        $this->iv = hex2bin("2811da22377d62fcfdb02f29aad77d9e");
    }

    function hex2bin($hexdata)
    {
        $bindata = "";

        for ($i = 0; $i < strlen($hexdata); $i += 2) {
            $bindata .= chr(hexdec(substr($hexdata, $i, 2)));
        }

        return $bindata;
    }

    function toPkcs7($value)
    {
        if (is_null($value))
            $value = "";

        $padSize = 16 - (strlen($value) % 16);
        return $value . str_repeat(chr($padSize), $padSize);
    }

    function fromPkcs7($value)
    {
        $valueLen = strlen($value);
        if ($valueLen % 16 > 0)
            $value = "";
        $padSize = ord($value{$valueLen - 1});
        if (($padSize < 1) or ($padSize > 16))
            $value = "";
        // Check padding.
        for ($i = 0; $i < $padSize; $i++) {
            if (ord($value{$valueLen - $i - 1}) != $padSize)
                $value = "";
        }
        return substr($value, 0, $valueLen - $padSize);
    }

    function encrypt($value, $key, $iv)
    {
        if (is_null($value))
            $value = "";
        $value = $this->toPkcs7($value);
        $output = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key,
            $value, MCRYPT_MODE_CBC, $iv);
        return base64_encode($output);
    }

    function decrypt($value, $key, $iv)
    {
        if (is_null($value))
            $value = "";
        $value = base64_decode($value);
        $output = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key,
            $value, MCRYPT_MODE_CBC, $iv);
        return $this->fromPkcs7($output);
    }
}