<?php

namespace Kaiser\Manager;

class DBManager
{
    var $enableLogging = true;

    const DB_PARAM_SCALAR = 1;
    const DB_PARAM_OPAQUE = 2;
    const DB_PARAM_MISC = 3;

    const DB_AUTO_INSERT = 1;
    const DB_AUTO_UPDATE = 2;
    const DB_AUTO_REPLACE = 3;

    private $pdo = null;
    private $last_query = null;

    function __construct($pdo = null)
    {
        $this->pdo = $pdo;
        $this->debug(sprintf('DBManager Class "%s" Initialized ', get_class($this)));
    }

    function __destruct()
    {
        // parent::__destruct ();
    }

    public function getPdo()
    {
        return $this->pdo;
    }

    protected function debug($message, array $context = array())
    {
        if ($this->enableLogging)
            logger($message, $context);
    }

    protected function err($message, array $context = array())
    {
        logger($message, $context);
    }

    protected function executeEmulateQuery($query, $data = array())
    {
        $this->_prepareEmulateQuery($query);

        // $stmt = ( int ) $stmt;
        $data = ( array )$data;
        $this->last_parameters = $data;

        if (count($this->prepare_types) != count($data)) {
            // throw new DBException ( $e->getMessage () );
            return false;
        }

        $realquery = $this->prepare_tokens [0];

        $i = 0;
        foreach ($data as $value) {
            if ($this->prepare_types [$i] == self::DB_PARAM_SCALAR) {
                $realquery .= $this->quote($value);
            } elseif ($this->prepare_types [$i] == self::DB_PARAM_OPAQUE) {
                $fp = @fopen($value, 'rb');
                if (!$fp) {
                    // return $this->raiseError ( DB_ERROR_ACCESS_VIOLATION );
                    // throw new DBException ( $e->getMessage () );
                    return false;
                }
                $realquery .= $this->quote(fread($fp, filesize($value)));
                fclose($fp);
            } else {
                $realquery .= $value;
            }

            $realquery .= $this->prepare_tokens [++$i];
        }

        return $realquery;
    }

    private function _prepareEmulateQuery($query)
    {
        $tokens = preg_split('/((?<!\\\)[&?!])/', $query, -1, PREG_SPLIT_DELIM_CAPTURE);
        $token = 0;
        $types = array();
        $newtokens = array();

        foreach ($tokens as $val) {
            switch ($val) {
                case '?' :
                    $types [$token++] = self::DB_PARAM_SCALAR;
                    break;
                case '&' :
                    $types [$token++] = self::DB_PARAM_OPAQUE;
                    break;
                case '!' :
                    $types [$token++] = self::DB_PARAM_MISC;
                    break;
                default :
                    $newtokens [] = preg_replace('/\\\([&?!])/', "\\1", $val);
            }
        }

        $this->prepare_tokens = &$newtokens;
        $this->prepare_types = $types;
        $this->prepared_queries = implode(' ', $newtokens);

        return $tokens;
    }

    function executePreparedQueryOne($statement, $params = array())
    {
        $sql = $this->executeEmulateQuery($statement, $params);
        $this->debug($sql);
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $result = $stmt->fetch(\PDO::FETCH_COLUMN);
        } catch (\PDOException $e) {
            // $this->err ( $this->last_query );
            $this->err($e->getMessage());
        }
        return false;
    }

    function executePreparedQueryToMap($statement, $params = array())
    {
        $sql = $this->executeEmulateQuery($statement, $params);
        $this->debug($sql);
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            // $this->err ( $this->last_query );
            $this->err($e->getMessage());
        }
        return false;
    }

    function executePreparedQueryToMapList($statement, $params = array())
    {
        $sql = $this->executeEmulateQuery($statement, $params);
        $this->debug($sql);
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            // $this->err ( $this->last_query );
            $this->err($e->getMessage());
        }
        return false;
    }

    function executePreparedQueryToArrayList($statement, $params = array())
    {
        $sql = $this->executeEmulateQuery($statement, $params);
        $this->debug($sql);
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $result = $stmt->fetchAll(\PDO::FETCH_NUM);
        } catch (\PDOException $e) {
            // $this->err ( $this->last_query );
            $this->err($e->getMessage());
        }
        return false;
    }

    function executePreparedQueryToObjList($statement, $params = array())
    {
        $sql = $this->executeEmulateQuery($statement, $params);
        $this->debug($sql);
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $result = $stmt->fetchAll(\PDO::FETCH_OBJ);
        } catch (\PDOException $e) {
            // $this->err ( $this->last_query );
            $this->err($e->getMessage());
        }
        return false;
    }

    function executePreparedUpdate($statement, $params = array())
    {
        $sql = $this->executeEmulateQuery($statement, $params);
        $this->debug($sql);
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $result = $this->pdo->lastInsertId();
            return $result === '0' ? $stmt->rowCount() : $result;
        } catch (\PDOException $e) {
            // $this->err ( $this->last_query );
            $this->err($e->getMessage());
        }
        return false;
    }

    function executeTransaction()
    {
        try {
            // Begin the PDO transaction
            $this->pdo->beginTransaction();

            // If no errors have been thrown or the transaction wasn't completed within
            // the closure, commit the changes
            $this->pdo->commit();

            return $this;
        } catch (\PDOException $e) {
            // something happened, rollback changes
            $this->pdo->rollBack();
            return $this;
        }
    }

    function AutoExecuteInsert($table_name, $fields_values, $where = false)
    {
        $ret = $this->_buildManipSQL($table_name, $fields_values, self::DB_AUTO_INSERT, $where);
        return $this->executePreparedUpdate($ret ['query'], $ret ['params']);
    }

    function AutoExecuteUpdate($table_name, $fields_values, $where = false)
    {
        $ret = $this->_buildManipSQL($table_name, $fields_values, self::DB_AUTO_UPDATE, $where);
        return $this->executePreparedUpdate($ret ['query'], $ret ['params']);
    }

    function AutoExecuteReplace($table_name, $fields_values, $where = false)
    {
        $ret = $this->_buildManipSQL($table_name, $fields_values, self::DB_AUTO_REPLACE, $where);
        return $this->executePreparedUpdate($ret ['query'], $ret ['params']);
    }

    private function _buildManipSQL($table, $table_fields, $mode, $where = false)
    {
        if (count($table_fields) == 0) {
            return false;
        }
        $first = true;
        $params = array();
        switch ($mode) {
            case self::DB_AUTO_INSERT :
                $values = '';
                $fields = '';
                foreach ($table_fields as $field => $value) {
                    $params [] = $value;
                    if ($first) {
                        $first = false;
                    } else {
                        $fields .= ',';
                        $values .= ',';
                    }
                    $fields .= $field;
                    $values .= '?';
                }
                $sql = "INSERT INTO $table ($fields) VALUES ($values)";
                return array(
                    'query' => $sql,
                    'params' => $params
                );
            case self::DB_AUTO_UPDATE :
                $set = '';
                foreach ($table_fields as $field => $value) {
                    $params [] = $value;
                    if ($first) {
                        $first = false;
                    } else {
                        $set .= ',';
                    }
                    $set .= "$field = ?";
                }
                $sql = "UPDATE $table SET $set";
                if ($where) {
                    $sql .= " WHERE $where";
                }
                return array(
                    'query' => $sql,
                    'params' => $params
                );
            case self::DB_AUTO_REPLACE :
                $values = '';
                $fields = '';
                foreach ($table_fields as $field => $value) {
                    $params [] = $value;
                    if ($first) {
                        $first = false;
                    } else {
                        $fields .= ',';
                        $values .= ',';
                    }
                    $fields .= $field;
                    $values .= '?';
                }
                $sql = "REPLACE INTO $table ($fields) VALUES ($values)";
                return array(
                    'query' => $sql,
                    'params' => $params
                );
            default :
                return false;
        }
    }
}