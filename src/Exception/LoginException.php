<?php

namespace Kaiser\Exception;

class LoginException extends DefaultException {
	function LoginException() {
		parent::__construct ( '아이디 또는 비밀번호가 일치하지 않습니다.' );
// 		parent::__construct ( '계정이 없거나 암호가 잘못되었습니다!' );
	}
}