<?php

namespace Kaiser\Manager;

class FtpManager {
	protected $FTP = null;
	function __construct($FTP) {
		$this->FTP = $FTP;
	}
	//
	private function parse_ftp_scanline($rawlistline) {
		// -------------------------------------------------------------------------
		// Scanning:
		// 1. first scan with strict rules
		// 2. if that does not match, scan with less strict rules
		// 3. if that does not match, scan with rules for specific FTP servers (AS400)
		// 4. and if that does not match, return the raw line
		// -------------------------------------------------------------------------
		
		// ----------------------------------------------
		// 1. Strict rules
		// ----------------------------------------------
		if (preg_match ( "/([-dl])([rwxsStT-]{9})[ ]+([0-9]+)[ ]+([^ ]+)[ ]+(.+)[ ]+([0-9]+)[ ]+([a-zA-Z]+[ ]+[0-9]+)[ ]+([0-9:]+)[ ]+(.*)/", $rawlistline, $regs ) == true) {
			// permissions number owner group size month day year/hour filename
			$listline ["scanrule"] = "rule-1";
			$listline ["dirorfile"] = "$regs[1]"; // Directory ==> d, File ==> -
			$listline ["dirfilename"] = "$regs[9]"; // Filename
			$listline ["files_inside"] = "$regs[3]";
			$listline ["size"] = "$regs[6]"; // Size
			$listline ["owner"] = "$regs[4]"; // Owner
			$listline ["group"] = trim ( $regs [5] ); // Group
			$listline ["permissions"] = "$regs[2]"; // Permissions
			$listline ["mtime"] = "$regs[7] $regs[8]"; // Mtime -- format depends on what FTP server returns (year, month, day, hour, minutes... see above)
		}		

		// ----------------------------------------------
		// 2. Less strict rules
		// ----------------------------------------------
		elseif (preg_match ( "/([-dl])([rwxsStT-]{9})[ ]+(.*)[ ]+([a-zA-Z0-9 ]+)[ ]+([0-9:]+)[ ]+(.*)/", $rawlistline, $regs ) == true) {
			// permissions number/owner/group/size
			// month-day year/hour filename
			$listline ["scanrule"] = "rule-2";
			$listline ["dirorfile"] = "$regs[1]"; // Directory ==> d, File ==> -
			$listline ["dirfilename"] = "$regs[6]"; // Filename
			$listline ["size"] = "$regs[3]"; // Number/Owner/Group/Size
			$listline ["permissions"] = "$regs[2]"; // Permissions
			$listline ["mtime"] = "$regs[4] $regs[5]"; // Mtime -- format depends on what FTP server returns (year, month, day, hour, minutes... see above)
		}		

		// ----------------------------------------------
		// 3. Specific FTP server rules
		// ----------------------------------------------
		
		// ---------------
		// 3.1 Windows
		// ---------------
		elseif (preg_match ( "/([0-9\\/-]+)[ ]+([0-9:AMP]+)[ ]+([0-9]*|<DIR>)[ ]+(.*)/", $rawlistline, $regs ) == true) {
			// date time size filename
			
			$listline ["scanrule"] = "rule-3.1";
			if ($regs [3] == "<DIR>") {
				$listline ["size"] = "";
			} else {
				$listline ["size"] = "$regs[3]";
			} // Size
			$listline ["dirfilename"] = "$regs[4]"; // Filename
			$listline ["owner"] = ""; // Owner
			$listline ["group"] = ""; // Group
			$listline ["permissions"] = ""; // Permissions
			$listline ["mtime"] = "$regs[1] $regs[2]"; // Mtime -- format depends on what FTP server returns (year, month, day, hour, minutes... see above)
			
			if ($listline ["size"] != "") {
				$listline ["dirorfile"] = "-";
			} else {
				$listline ["dirorfile"] = "d";
			}
		}		

		// ---------------
		// 3.2 Netware
		// Thanks to Danny!
		// ---------------
		elseif (preg_match ( "/([-]|[d])[ ]+(.{10})[ ]+([^ ]+)[ ]+([0-9]*)[ ]+([a-zA-Z]*[ ]+[0-9]*)[ ]+([0-9:]*)[ ]+(.*)/", $rawlistline, $regs ) == true) {
			// dir/file perms owner size month day hour filename
			$listline ["scanrule"] = "rule-3.2";
			$listline ["dirorfile"] = "$regs[1]"; // Directory ==> d, File ==> -
			$listline ["dirfilename"] = "$regs[7]"; // Filename
			$listline ["size"] = "$regs[4]"; // Size
			$listline ["owner"] = "$regs[3]"; // Owner
			$listline ["group"] = ""; // Group
			$listline ["permissions"] = "$regs[2]"; // Permissions
			$listline ["mtime"] = "$regs[5] $regs[6]"; // Mtime -- format depends on what FTP server returns (year, month, day, hour, minutes... see above)
		}		

		// ---------------
		// 3.3 AS400
		// ---------------
		elseif (preg_match ( "/([a-zA-Z0-9_-]+)[ ]+([0-9]+)[ ]+([0-9\\/-]+)[ ]+([0-9:]+)[ ]+([a-zA-Z0-9_ -\*]+)[ \\/]+([^\\/]+)/", $rawlistline, $regs ) == true) {
			// owner size date time type filename
			
			if ($regs [5] != "*STMF") {
				$directory_or_file = "d";
			} elseif ($regs [5] == "*STMF") {
				$directory_or_file = "-";
			}
			
			$listline ["scanrule"] = "rule-3.3";
			$listline ["dirorfile"] = "$directory_or_file"; // Directory ==> d, File ==> -
			$listline ["dirfilename"] = "$regs[6]"; // Filename
			$listline ["size"] = "$regs[2]"; // Size
			$listline ["owner"] = "$regs[1]"; // Owner
			$listline ["group"] = ""; // Group
			$listline ["permissions"] = ""; // Permissions
			$listline ["mtime"] = "$regs[3] $regs[4]"; // Mtime -- format depends on what FTP server returns (year, month, day, hour, minutes... see above)
		}		

		// ---------------
		// 3.4 Titan
		// Owner, group are modified compared to rule 1
		// TODO: integrate this rule in rule 1 itself
		// ---------------
		elseif (preg_match ( "/([-dl])([rwxsStT-]{9})[ ]+([0-9]+)[ ]+([a-zA-Z0-9]+)[ ]+([a-zA-Z0-9]+)[ ]+([0-9]+)[ ]+([a-zA-Z]+[ ]+[0-9]+)[ ]+([0-9:]+)[ ](.*)/", $rawlistline, $regs ) == true) {
			// dir/file permissions number owner group size month date time file
			$listline ["scanrule"] = "rule-3.4";
			$listline ["dirorfile"] = "$regs[1]"; // Directory ==> d, File ==> -
			$listline ["dirfilename"] = "$regs[9]"; // Filename
			$listline ["size"] = "$regs[6]"; // Size
			$listline ["owner"] = "$regs[4]"; // Owner
			$listline ["group"] = "$regs[5]"; // Group
			$listline ["permissions"] = "$regs[2]"; // Permissions
			$listline ["mtime"] = "$regs[7] $regs[8]"; // Mtime -- format depends on what FTP server returns (year, month, day, hour, minutes... see above)
		} 		

		// ----------------------------------------------
		// 4. If nothing matchs, return the raw line
		// ----------------------------------------------
		else {
			$listline ["scanrule"] = "rule-4";
			$listline ["dirorfile"] = "u";
			$listline ["dirfilename"] = $rawlistline;
		}
		
		// -------------------------------------------------------------------------
		// Remove the . and .. entries
		// Remove the total line that some servers return
		// -------------------------------------------------------------------------
		if ($listline ["dirfilename"] == "." || $listline ["dirfilename"] == "..") {
			return "";
		} elseif (substr ( $rawlistline, 0, 5 ) == "total") {
			return "";
		}
		
		// -------------------------------------------------------------------------
		// And finally... return the nice list!
		// -------------------------------------------------------------------------
		return $listline;
	}
	private function parse_ftp_rawlist($path = null) {
		try {
			$list = $this->FTP->rawlist ( $path );
		} catch ( Exception $e ) {
			// $this->err ( $e->getMessage () );
			throw new FtpException ( $e->getMessage () );
			return false;
		}
		
		if (! is_array ( $list )) {
			return array (
					'dirs' => array (),
					'files' => array () 
			);
		}
		
		// include ANIME_PATH . DS . 'include/include.js.php';
		$dirs_list = array ();
		$files_list = array ();
		
		foreach ( $list as $line ) {
			$listline = $this->parse_ftp_scanline ( $line );
			
			if ($listline == "") {
				continue;
			}
			
			$isdir = ($listline ['dirorfile'] === 'd');
			$stamp = strtotime ( $listline ['mtime'] );
			$ext = pathinfo ( $listline ['dirfilename'], PATHINFO_EXTENSION ) ?  : 'unknown';
			
			if ($listline ["dirorfile"] == "d") {
				$dirs_list [] = array (
						'rights' => $listline ['permissions'],
						'files_inside' => $listline ['files_inside'],
						'owner' => $listline ['owner'],
						'group' => $listline ['group'],
						'size' => $listline ['size'],
						'date' => $listline ['mtime'],
						'name' => $listline ['dirfilename'],
						'stamp' => $stamp 
				);
			} else {
				$files_list [] = array (
						'rights' => $listline ['permissions'],
						'owner' => $listline ['owner'],
						'group' => $listline ['group'],
						'size' => $listline ['size'],
						'date' => $listline ['mtime'],
						'name' => $listline ['dirfilename'],
						'stamp' => $stamp,
						'ext' => $ext,
						'type' => \Kaiser\Manager\Extension\Type::getInstance ()->get ( $ext ),
						'base64' => base64_encode ( $listline ['dirfilename'] ) 
				);
			}
		}
		
		return array (
				'dirs' => (is_array ( $dirs_list )) ? $dirs_list : array (),
				'files' => (is_array ( $files_list )) ? $files_list : array () 
		);
	}
	private function sort_on_number($by = 'size', $order = 'ASC') {
		if ($order == 'DESC')
			return function ($a, $b) use($by) {
				return $b [$by] - $a [$by];
			};
		else
			return function ($a, $b) use($by) {
				return $a [$by] - $b [$by];
			};
	}
	private function sort_on_string($by = 'name', $order = 'ASC') {
		if ($order == 'DESC')
			return function ($a, $b) use($by) {
				return - strnatcmp ( $a [$by], $b [$by] );
			};
		else
			return function ($a, $b) use($by) {
				return strnatcmp ( $a [$by], $b [$by] );
			};
	}
	private function sort_on_field($result, $by = 'name', $order = 'ASC') {
		if ($result == false) {
			return false;
		}
		switch ($by) {
			case 'size' :
			case 'stamp' :
				usort ( $result, $this->sort_on_number ( $by, $order ) );
				return $result;
			default :
				usort ( $result, $this->sort_on_string ( $by, $order ) );
				return $result;
		}
	}
	protected function _lsDirs($path) {
		$list = $this->parse_ftp_rawlist ( $path );
		if ($list == false) {
			return false;
		}
		return $list ["dirs"];
	}
	protected function _lsFiles($path) {
		$list = $this->parse_ftp_rawlist ( $path );
		if ($list == false) {
			return false;
		}
		return $list ["files"];
	}
	protected function _lsBoth($path) {
		$list = $this->parse_ftp_rawlist ( $path );
		if ($list == false) {
			return false;
		}
		return $list;
	}
	private function queryAllToArrayList($path) {
		try {
			$list = $this->FTP->rawlist ( $path );
		} catch ( Exception $e ) {
			// $this->err ( $e->getMessage () );
			throw new FtpException ( $e->getMessage () );
			return false;
		}
		
		$rawlist = join ( "\n", $list );
		preg_match_all ( '/^([drwx+-]{10})\s+(\d+)\s+(\w+)\s+(\w+)\s+(\d+)\s+(.{12}) (.*)$/m', $rawlist, $matches, PREG_SET_ORDER );
		return ! empty ( $matches ) ? $matches : false;
	}
	protected function queryDirToMapList($path, $by = 'name', $order = 'ASC') {
		$result = $this->_lsDirs ( $path );
		return $this->sort_on_field ( $result, $by, $order );
	}
	protected function queryFileToMapList($path, $by = 'name', $order = 'ASC') {
		$result = $this->_lsFiles ( $path );
		return $this->sort_on_field ( $result, $by, $order );
	}
	protected function queryAllToMapList($path, $by = 'name', $order = 'ASC') {
		$result = $this->_lsBoth ( $path );
		return $this->sort_on_field ( $result, $by, $order );
	}
}
