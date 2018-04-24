<?php
/**
 * Created by PhpStorm.
 * User: 김명철
 * Date: 2018-04-22
 * Time: 오전 7:36
 */

namespace Kaiser;


class Auth
{
    var $admin = [
        'username' => 'admin',//사용자명(아이디)
        'password' => 'X',//패스워드
        'uid' => '0',//사용자ID
        'gid' => '0',//그룹ID
        'comment' => '',//정보
        'home' => '/admin',//홈디렉토리
        'defaultPage' => '/admin',//홈페이지
        'hashed_password' => '',//해쉬비밀번호
        'salt' => '',//비밀번호암호키
    ];
}