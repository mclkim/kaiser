<?php

namespace Kaiser;

class Plupload extends \PluploadHandler
{
    var $_extensions_mode = 'deny';
    var $_extensions_check = array(
        'php',
        'phtm',
        'phtml',
        'php3',
        'inc'
    );

    function __construct($conf = array())
    {
        self::$conf = array_merge(array(
            'file_data_name' => 'file',
            'tmp_dir' => ini_get("upload_tmp_dir") . DIRECTORY_SEPARATOR . "plupload",
            'target_dir' => false,
            'cleanup' => true,
            'max_file_age' => 5 * 3600,
            'chunk' => isset ($_REQUEST ['chunk']) ? intval($_REQUEST ['chunk']) : 0,
            'chunks' => isset ($_REQUEST ['chunks']) ? intval($_REQUEST ['chunks']) : 0,
            'file_name' => isset ($_REQUEST ['name']) ? $_REQUEST ['name'] : false,
            'tmp_name' => false,
            'allow_extensions' => false,
            'delay' => 0,
            'cb_sanitize_file_name' => array(__CLASS__, 'sanitize_file_name'),
            'cb_check_file' => false
        ), $conf);
    }

    /**
     */
    function getFiles()
    {
        //TODO::다른 방법없을까??
        self::$_error = null; // start fresh

        $conf = self::$conf;

        try {
            if (!$conf ['file_name']) {
                if (!empty ($_FILES)) {
                    $conf ['file_name'] = $_FILES [$conf ['file_data_name']] ['name'];
                } else {
                    throw new \Exception ('', PLUPLOAD_INPUT_ERR);
                }
            }
            if (!$conf ['tmp_name']) {
                if (!empty ($_FILES)) {
                    $tmp_name = $_FILES [$conf ['file_data_name']] ['tmp_name'];
                    $size = $_FILES [$conf ['file_data_name']] ['size'];
                    $type = $_FILES [$conf ['file_data_name']] ['type'];
                } else {
                    throw new \Exception ('', PLUPLOAD_INPUT_ERR);
                }
            }

            if (is_callable($conf ['cb_sanitize_file_name'])) {
                $file_name = call_user_func($conf ['cb_sanitize_file_name'], $conf ['file_name']);
            } else {
                $file_name = $conf ['file_name'];
            }

            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            // Check if file type is allowed
            if ($conf ['allow_extensions']) {
                if (is_string($conf ['allow_extensions'])) {
                    $conf ['allow_extensions'] = preg_split('{\s*,\s*}', $conf ['allow_extensions']);
                }

                if (!in_array($file_ext, $conf ['allow_extensions'])) {
                    throw new \Exception ('', PLUPLOAD_TYPE_ERR);
                }
            }

            // Valid extensions check
            if (!$this->_evalValidExtensions($file_ext)) {
                throw new \Exception ('', PLUPLOAD_TYPE_ERR);
            }

            return array(
                'name' => $conf ['file_name'],
                'real' => $file_name,
                'form_name' => $conf ['file_data_name'],
                'ext' => $file_ext,
                'tmp_name' => $tmp_name,
                'size' => $size,
                'type' => $type
            );
        } catch (\Exception $ex) {
            self::$_error = $ex->getCode();
            return false;
        }
    }

    /**
     */
    function upload($file_name)
    {
        // 5 minutes execution time
        @set_time_limit(5 * 60);

        self::$_error = null; // start fresh

        $conf = self::$conf;

        try {
            // Cleanup outdated temp files and folders
            //TODO::다른 방법없을까??
            if ($conf ['cleanup']) {
                self::cleanup();
            }

            // Fake network congestion
            if ($conf ['delay']) {
                usleep($conf ['delay']);
            }

            if (($file = self::getFiles()) == false)
                throw new \Exception ('', PLUPLOAD_INPUT_ERR);

            /**
             * TODO:: 한글변환을 해야 한다.
             */
            // $file_name = iconv("utf-8", "euc-kr", $file ['name']);
            $file_path = rtrim($conf ['target_dir'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $file_name;
            $tmp_path = $file_path . ".part";

            // Write file or chunk to appropriate temp location
            if ($conf ['chunks']) {
                self::write_file_to("$file_path.dir.part" . DIRECTORY_SEPARATOR . $conf ['chunk']);

                // Check if all chunks already uploaded
                if ($conf ['chunk'] == $conf ['chunks'] - 1) {
                    self::write_chunks_to_file("$file_path.dir.part", $tmp_path);
                }
            } else {
                self::write_file_to($tmp_path);
            }

            // Upload complete write a temp file to the final destination
            if (!$conf ['chunks'] || $conf ['chunk'] == $conf ['chunks'] - 1) {
                if (is_callable($conf ['cb_check_file']) && !call_user_func($conf ['cb_check_file'], $tmp_path)) {
                    @unlink($tmp_path);
                    throw new \Exception ('', PLUPLOAD_SECURITY_ERR);
                }

                rename($tmp_path, $file_path);

                return array(
                    'real' => $file_name,
                    'path' => $file_path,
                    'size' => filesize($file_path)
                );
            }

            // ok so far
            return true;
        } catch (\Exception $ex) {
            self::$_error = $ex->getCode();
            return false;
        }
    }

    /**
     * Function to restrict the valid extensions on file uploads
     *
     * @param array $exts
     *            File extensions to validate
     * @param string $mode
     *            The type of validation:
     *            1) 'deny' Will deny only the supplied extensions
     *            2) 'accept' Will accept only the supplied extensions
     *            as valid
     * @access public
     */
    function setValidExtensions($exts, $mode = 'deny')
    {
        $this->_extensions_check = $exts;
        $this->_extensions_mode = $mode;
    }

    /**
     * Evaluates the validity of the extensions set by setValidExtensions
     *
     * @return bool False on non valid extension, true if they are valid
     * @access private
     */
    private function _evalValidExtensions($ext)
    {
        $exts = $this->_extensions_check;
        settype($exts, 'array');
        if ($this->_extensions_mode == 'deny') {
            if (in_array($ext, $exts)) {
                return false;
            }
            // mode == 'accept'
        } else {
            if (!in_array($ext, $exts)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Sanitizes a filename replacing whitespace with dashes
     *
     * Removes special characters that are illegal in filenames on certain
     * operating systems and special characters requiring special escaping
     * to manipulate at the command line. Replaces spaces and consecutive
     * dashes with a single dash. Trim period, dash and underscore from beginning
     * and end of filename.
     */
    private static function sanitize_file_name($filename)
    {
        /**
         * TODO:: 한글변환을 해야 한다.
         */
        //$filename = iconv("utf-8", "euc-kr", $filename);
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $filename = 'x.' . $file_ext;

        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)) . '_' . $filename;
    }
}

