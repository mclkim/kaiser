<?php

namespace Kaiser;

class Response extends Singleton {
	protected $version;
	protected $statusCode;
	protected $statusText;
	public function setContent($content) {
		if (null !== $content && ! is_string ( $content ) && ! is_numeric ( $content ) && ! is_callable ( array (
				$content,
				'__toString' 
		) )) {
			throw new \UnexpectedValueException ( sprintf ( 'The Response content must be a string or object implementing __toString(), "%s" given.', gettype ( $content ) ) );
		}
		
		$this->content = ( string ) $content;
		
		return $this;
	}
	public function __toString() {
		return sprintf ( 'HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText ) . "\r\n" . $this->headers . "\r\n" . $this->getContent ();
	}
	public function getContent() {
		return $this->content;
	}
}
