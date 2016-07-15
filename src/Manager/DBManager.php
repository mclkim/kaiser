<?php

namespace Kaiser\Manager;

use Pixie\QueryBuilder\QueryBuilderHandler;
use Pixie\QueryBuilder\QueryObject;

// https://github.com/usmanhalalit/pixie
class DBManager extends \Pixie\QueryBuilder\QueryBuilderHandler
{
    var $enableLogging = true;

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
    function AutoExecuteInsert($table, $data)
    {
        $instance = $this->table($table);
        $queryObject = $instance->getQuery('insert', $data);
        $sql = $queryObject->getSql();
        $params = $queryObject->getBindings();
        list($result) = $this->statement($sql, $params);
        $this->debug($this->executeEmulateQuery($sql, $params));
        $res = $this->pdo->lastInsertId();
        return $res === '0' ? $result->rowCount() : $res;

    }

    function AutoExecuteUpdate($table, $data, $key1, $oper1 = null, $val1 = null, $key2 = null, $oper2 = null, $val2 = null)
    {
        if (func_num_args() == 4) {
            return $this->table($table)->where($key1, '=', $oper1)->update($data);
        } else if (func_num_args() == 5) {
            return $this->table($table)->where($key1, $oper1, $val1)->update($data);
        } else if (func_num_args() == 6) {
            return $this->table($table)->where($key1, '=', $oper1)->where($val1, '=', $key2)->update($data);
        } else if (func_num_args() == 8) {
            return $this->table($table)->where($key1, $oper1, $val1)->where($key2, $oper2, $val2)->update($data);
        }
    }
}