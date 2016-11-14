<?php

// 비대칭 알고리듬인 RSA를 사용하여 문자열을 암호화하는 법.
// 개인키 비밀번호는 암호화할 때는 필요없고 복호화할 때만 입력하면 되므로
// 서버에 저장할 필요 없이 그때그때 관리자가 입력하도록 해도 된다.
// PHP 5.2 이상, openssl 모듈이 필요하다.

// RSA 개인키, 공개키 조합을 생성한다.
// 키 생성에는 시간이 많이 걸리므로, 한 번만 생성하여 저장해 두고 사용하면 된다.
// 단, 비밀번호는 개인키를 사용할 때마다 필요하다.

/**
 * https://gist.github.com/kijin/8573062
 */

namespace Kaiser\Session;

class rsaCrypt
{
    function rsa_generate_keys($password, $bits = 2048, $digest_algorithm = 'sha256')
    {
        $res = openssl_pkey_new(array(
            'digest_alg' => $digest_algorithm,
            'private_key_bits' => $bits,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ));

        openssl_pkey_export($res, $private_key, $password);

        $public_key = openssl_pkey_get_details($res);
        $public_key = $public_key['key'];

        return array(
            'private_key' => $private_key,
            'public_key' => $public_key,
        );
    }

// RSA 공개키를 사용하여 문자열을 암호화한다.
// 암호화할 때는 비밀번호가 필요하지 않다.
// 오류가 발생할 경우 false를 반환한다.

    function rsa_encrypt($plaintext, $public_key)
    {
        // 용량 절감과 보안 향상을 위해 평문을 압축한다.

        $plaintext = gzcompress($plaintext);

        // 공개키를 사용하여 암호화한다.

        $pubkey_decoded = @openssl_pkey_get_public($public_key);
        if ($pubkey_decoded === false) return false;

        $ciphertext = false;
        $status = @openssl_public_encrypt($plaintext, $ciphertext, $pubkey_decoded);
        if (!$status || $ciphertext === false) return false;

        // 암호문을 base64로 인코딩하여 반환한다.

        return base64_encode($ciphertext);
    }

// RSA 개인키를 사용하여 문자열을 복호화한다.
// 복호화할 때는 비밀번호가 필요하다.
// 오류가 발생할 경우 false를 반환한다.

    function rsa_decrypt($ciphertext, $private_key, $password)
    {
        // 암호문을 base64로 디코딩한다.

        $ciphertext = @base64_decode($ciphertext, true);
        if ($ciphertext === false) return false;

        // 개인키를 사용하여 복호화한다.

        $privkey_decoded = @openssl_pkey_get_private($private_key, $password);
        if ($privkey_decoded === false) return false;

        $plaintext = false;
        $status = @openssl_private_decrypt($ciphertext, $plaintext, $privkey_decoded);
        @openssl_pkey_free($privkey_decoded);
        if (!$status || $plaintext === false) return false;

        // 압축을 해제하여 평문을 얻는다.

        $plaintext = @gzuncompress($plaintext);
        if ($plaintext === false) return false;

        // 이상이 없는 경우 평문을 반환한다.

        return $plaintext;
    }
}