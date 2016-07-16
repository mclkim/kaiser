<?php

namespace Kaiser\Manager;

use Pixie\QueryBuilder\QueryBuilderHandler;
use Pixie\QueryBuilder\QueryObject;

// https://github.com/usmanhalalit/pixie
class DBManager extends \Pixie\QueryBuilder\QueryBuilderHandler
{
    var $enableLogging = true;

    const DB_AUTO_INSERT = 1;
    const DB_AUTO_UPDATE = 2;
    const DB_AUTO_REPLACE = 3;

    protected function debug($message, array $context = array())
    {
        if ($this->enableLogging)
            logger($message, $context);
    }

    function executeEmulateQuery($query, $params = array())
    {
        $query = new QueryObject ($query, $params, $this->pdo);
        return $query->getRawSql();
    }

    function executePreparedQueryOne($sql, $params = array())
    {
        $this->debug($this->executeEmulateQuery($sql, $params));
        try {
            $query = $this->query($sql, $params);
            $result = $query->setFetchMode(\PDO::FETCH_COLUMN)->first();
        } catch (PDOException $e) {
            throw new DBException ($e->getMessage());
        } catch (\Exception $e) {
        }
        return $result;
    }

    function executePreparedQueryToMap($sql, $params = array())
    {
        $this->debug($this->executeEmulateQuery($sql, $params));
        try {
            $query = $this->query($sql, $params);
            $result = $query->setFetchMode(\PDO::FETCH_ASSOC)->first();
        } catch (PDOException $e) {
            throw new DBException ($e->getMessage());
        } catch (\Exception $e) {
        }
        return $result;
    }

    function executePreparedQueryToMapList($sql, $params = array())
    {
        $this->debug($this->executeEmulateQuery($sql, $params));
        try {
            $query = $this->query($sql, $params);
            $result = $query->setFetchMode(\PDO::FETCH_ASSOC)->get();
        } catch (PDOException $e) {
            throw new DBException ($e->getMessage());
        } catch (\Exception $e) {
        }
        return $result;
    }

    function executePreparedQueryToArrayList($sql, $params = array())
    {
        $this->debug($this->executeEmulateQuery($sql, $params));
        try {
            $query = $this->query($sql, $params);
            $result = $query->setFetchMode(\PDO::FETCH_NUM)->get();
        } catch (PDOException $e) {
            throw new DBException ($e->getMessage());
        } catch (\Exception $e) {
        }
        return $result;
    }


    function executePreparedQueryToObjList($sql, $params = array())
    {
        $this->debug($this->executeEmulateQuery($sql, $params));
        try {
            $query = $this->query($sql, $params);
            $result = $query->setFetchMode(\PDO::FETCH_OBJ)->get();
        } catch (PDOException $e) {
            throw new DBException ($e->getMessage());
        } catch (\Exception $e) {
        }
        return $result;
    }

    function executePreparedUpdate($sql, $params = array())
    {
        $this->debug($this->executeEmulateQuery($sql, $params));
        try {
            list($result) = $this->statement($sql, $params);
            $res = $this->pdo->lastInsertId();
            return $res === '0' ? $result->rowCount() : $res;
        } catch (PDOException $e) {
            throw new DBException ($e->getMessage());
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
            // something happened, rollback changes
            $this->pdo->rollBack();
            return $this;
        }
    }

    /**
     * return $this->table($table)->insert($data);
     */
    // function AutoExecuteInsert($table, $data)
    // {
    //     $instance = $this->table($table);
    //     $queryObject = $instance->getQuery('insert', $data);
    //     $sql = $queryObject->getSql();
    //     $params = $queryObject->getBindings();
    //     list($result) = $this->statement($sql, $params);
    //     $this->debug($this->executeEmulateQuery($sql, $params));
    //     $res = $this->pdo->lastInsertId();
    //     return $res === '0' ? $result->rowCount() : $res;

    // }

    // function AutoExecuteUpdate($table, $data, $key1, $oper1 = null, $val1 = null, $key2 = null, $oper2 = null, $val2 = null)
    // {
    //     if (func_num_args() == 4) {
    //         return $this->table($table)->where($key1, '=', $oper1)->update($data);
    //     } else if (func_num_args() == 5) {
    //         return $this->table($table)->where($key1, $oper1, $val1)->update($data);
    //     } else if (func_num_args() == 6) {
    //         return $this->table($table)->where($key1, '=', $oper1)->where($val1, '=', $key2)->update($data);
    //     } else if (func_num_args() == 8) {
    //         return $this->table($table)->where($key1, $oper1, $val1)->where($key2, $oper2, $val2)->update($data);
    //     }
    // }
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