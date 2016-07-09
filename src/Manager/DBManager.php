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
        if (!is_array($params)) $params = array();
        $query = new QueryObject ($query, $params, $this->pdo);
        return $query->getRawSql();
    }

    function executePreparedQueryToMapList($sql, $params = array())
    {
        try {
            $query = $this->query($sql, $params);
            $result = $query->setFetchMode(\PDO::FETCH_ASSOC)->get();
//            $this->setFetchMode(\PDO::FETCH_CLASS);
//            $result = $query->get();
        } catch (PDOException $e) {
            throw new DBException ($e->getMessage());
        } catch (\Exception $e) {
        }
        return $result;
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

    function executePreparedQueryToArrayList($sql, $params = array())
    {
        $this->debug($this->executeEmulateQuery($sql, $params));
        try {
            $query = $this->query($sql, $params);
            $result = $query->setFetchMode(\PDO::FETCH_NUM)->get();
//            $this->setFetchMode(\PDO::FETCH_NUM);
//            $result = $query->get();
        } catch (PDOException $e) {
            throw new DBException ($e->getMessage());
        } catch (\Exception $e) {
        }
        return $result;
    }

    function executePreparedQueryToColList($sql, $params = array(), $col = 0)
    {
        $this->debug($this->executeEmulateQuery($sql, $params));
        try {
            $query = $this->query($sql, $params);
            $result = $query->setFetchMode(\PDO::FETCH_COLUMN)->get();
//            $this->setFetchMode(\PDO::FETCH_COLUMN);
//            $result = $query->get();
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
//            $this->setFetchMode(\PDO::FETCH_CLASS);
//            $result = $query->get();
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
//            $this->setFetchMode(\PDO::FETCH_OBJ);
//            $result = $query->get();
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
            $query = $this->query($sql, $params);
        } catch (PDOException $e) {
            throw new DBException ($e->getMessage());
        } catch (\Exception $e) {
        }
        // return $result;
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

    function AutoExecuteInsert($table, $data)
    {
        return $this->table($table)->insert($data);
    }

    function AutoExecuteUpdate($table, $data, $key, $operator = '=', $value = null)
    {
        return $this->table($table)->where($key, $operator, $value)->update($data);
    }
}