<?php
if (! function_exists ( "strcut_utf8" )) {
	// utf-8 형식 글 길이 지정하여 자르기
	function strcut_utf8($str, $len, $checkmb = false, $tail = '...') {
		$rtn = $str;
		if (mb_strlen ( $str, "UTF-8" ) > $len) {
			$rtn = mb_substr ( $str, 0, $len, "UTF-8" ) . $tail;
		}
		return $rtn;
	}
}
?>