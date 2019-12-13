<?php
/* TEMPLATE PLUGIN FUNCTION EXAMPLE */
function custom_substr($word, $length, $lastWord) {
	$wordCnt = mb_strlen ( $word, 'utf-8' );
	if ($wordCnt > $length) {
		return mb_substr ( $word, 0, $length, 'utf-8' ) . $lastWord;
	} else {
		return $word;
	}
}
?>