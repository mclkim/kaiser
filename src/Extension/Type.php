<?php

namespace Kaiser\Extension;

/**
 * -------------------------------------------------------------------------------
 *
 * -------------------------------------------------------------------------------
 */
class Type {
	protected static $instance;
	public function getTypes() {
		return array (
				'aac' => 'aac',
				'ace' => 'z',
				'ai' => 'ai',
				'aif' => 'aif',
				'aiff' => 'aiff',
				'alz' => 'alz',
				'arc' => 'arc',
				'arj' => 'arj',
				'asf' => 'asf',
				'asp' => 'asp',
				'avi' => 'avi',
				'b64' => 'b64',
				'bat' => 'bat',
				'bhx' => 'bhx',
				'bin' => 'bin',
				'bmp' => 'bmp',
				'bz' => 'bz',
				'bz2' => 'bz2',
				'cab' => 'cab',
				'cfm' => 'cfm',
				'class' => 'class',
				'com' => 'com',
				'css' => 'css',
				'doc' => 'doc',
				'docx' => 'doc',
				'dot' => 'dot',
				'dvi' => 'dvi',
				'eps' => 'eps',
				'exe' => 'exe',
				'fla' => 'fla',
				'gif' => 'gif',
				'gz' => 'gz',
				'h30' => 'h30',
				'hqx' => 'hqx',
				'htm' => 'htm',
				'html' => 'html',
				'hwp' => 'hwp',
				'ico' => 'ico',
				'indd' => 'indd',
				'ini' => 'ini',
				'java' => 'java',
				'jpeg' => 'jpeg',
				'jpg' => 'jpg',
				'js' => 'js',
				'log' => 'log',
				'lzh' => 'lzh',
				'mdb' => 'mdb',
				'mid' => 'mid',
				'midi' => 'midi',
				'mim' => 'mim',
				'moov' => 'moov',
				'mp2' => 'mp2',
				'mp3' => 'mp3',
				'mp4' => 'mp4',
				'mpeg' => 'mpeg',
				'mpg' => 'mpg',
				'mpp' => 'mpp',
				'mpt' => 'mpt',
				'mpv' => 'mpv',
				'mpx' => 'mpx',
				'msi' => 'msi',
				'out' => 'out',
				'pct' => 'pct',
				'pcx' => 'pcx',
				'pdf' => 'pdf',
				'php' => 'php',
				'pic' => 'pic',
				'pict' => 'pict',
				'pkg' => 'pkg',
				'pl' => 'pl',
				'png' => 'png',
				'pot' => 'pot',
				'ppa' => 'ppa',
				'ppc' => 'ppc',
				'pps' => 'pps',
				'ppt' => 'ppt',
				'pptx' => 'ppt',
				'psd' => 'psd',
				'pwc' => 'pwc',
				'qt' => 'qt',
				'ra' => 'ra',
				'ram' => 'ram',
				'rar' => 'rar',
				'rem' => 'rem',
				'rmi' => 'rmi',
				'rmp' => 'rmp',
				'rtf' => 'rtf',
				'rtx' => 'rtx',
				'sdp' => 'sdp',
				'sgml' => 'sgml',
				'shtml' => 'shtml',
				'smi' => 'smi',
				'snd' => 'snd',
				'sql' => 'sql',
				'swf' => 'swf',
				'sys' => 'sys',
				'tar' => 'tar',
				'taz' => 'taz',
				'text' => 'text',
				'tgz' => 'tgz',
				'tif' => 'tif',
				'tiff' => 'tiff',
				'txt' => 'txt',
				'tz' => 'tz',
				'uu' => 'uu',
				'uue' => 'uue',
				'wav' => 'wav',
				'wmf' => 'wmf',
				'wmv' => 'wmv',
				'word' => 'word',
				'wp' => 'wp',
				'wp5' => 'wp5',
				'wp6' => 'wp6',
				'wpd' => 'wpd',
				'wri' => 'wri',
				'xl' => 'xl',
				'xla' => 'xla',
				'xlb' => 'xlb',
				'xlc' => 'xlc',
				'xld' => 'xld',
				'xlk' => 'xlk',
				'xll' => 'xll',
				'xlm' => 'xlm',
				'xls' => 'xls',
				'xlsx' => 'xls',
				'xlt' => 'xlt',
				'xlv' => 'xlv',
				'xlw' => 'xlw',
				'xml' => 'xml',
				'xxe' => 'xxe',
				'z' => 'z',
				'zip' => 'zip',
				'7z' => '7z' 
		);
	}
	public static function getInstance() {
		if (! self::$instance) {
			self::$instance = new self ();
		}
		
		return self::$instance;
	}
	function get($id) {
		if (! $this->has ( $id )) {
			return 'unknown';
		}
		
		$type_extension = $this->getTypes ();
		return $type_extension [strtolower ( $id )];
	}
	function has($id) {
		$type_extension = $this->getTypes ();
		return isset ( $type_extension [strtolower ( $id )] );
	}
}