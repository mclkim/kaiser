<?php

namespace Kaiser\Manager;

use Pixie\QueryBuilder\QueryBuilderHandler;
use Pixie\QueryBuilder\QueryObject;
// https://github.com/usmanhalalit/pixie
class DBManager extends \Pixie\QueryBuilder\QueryBuilderHandler {
	protected function executeEmulateQuery($query, $params = array()) {
		$query = new QueryObject ( $query, $params, $this->pdo );
		return $query->getRawSql ();
	}
	function executePreparedQueryToMapList($sql, $params = array()) {
		$query = $this->query ( $sql, $params );
		try {
			$this->preparePdoStatement ();
			$result = $this->pdoStatement->fetchAll ( \PDO::FETCH_CLASS );
			$this->pdoStatement = null;
		} catch ( PDOException $e ) {
			throw new DBException ( $e->getMessage () );
		}
		return $result;
	}
	function executePreparedQueryOne($sql, $params = array()) {
		$query = $this->query ( $sql, $params );
		try {
			$this->preparePdoStatement ();
			$result = $this->pdoStatement->fetch ( \PDO::FETCH_COLUMN );
			$this->pdoStatement = null;
		} catch ( PDOException $e ) {
			throw new DBException ( $e->getMessage () );
		}
		return $result;
	}
	function executePreparedQueryToArrayList($sql, $params = array()) {
		$query = $this->query ( $sql, $params );
		try {
			$this->preparePdoStatement ();
			$result = $this->pdoStatement->fetchAll ( \PDO::FETCH_NUM );
			$this->pdoStatement = null;
		} catch ( PDOException $e ) {
			throw new DBException ( $e->getMessage () );
		}
		return $result;
	}
	function executePreparedQueryToColList($sql, $params = array(), $col = 0) {
		$query = $this->query ( $sql, $params );
		try {
			$this->preparePdoStatement ();
			$result = $this->pdoStatement->fetchAll ( \PDO::FETCH_COLUMN, $col );
			$this->pdoStatement = null;
		} catch ( PDOException $e ) {
			throw new DBException ( $e->getMessage () );
		}
		return $result;
	}
	function executePreparedQueryToMap($sql, $params = array()) {
		$query = $this->query ( $sql, $params );
		try {
			$this->preparePdoStatement ();
			$result = $this->pdoStatement->fetch ( \PDO::FETCH_ASSOC );
			$this->pdoStatement = null;
		} catch ( PDOException $e ) {
			throw new DBException ( $e->getMessage () );
		}
		return $result;
	}
	function executePreparedQueryToObjList($sql, $params = array()) {
		$query = $this->query ( $sql, $params );
		try {
			$this->preparePdoStatement ();
			$result = $this->pdoStatement->fetchAll ( \PDO::FETCH_OBJ );
			$this->pdoStatement = null;
		} catch ( PDOException $e ) {
			throw new DBException ( $e->getMessage () );
		}
		return $result;
	}
	function executePreparedUpdate($sql, $params = array()) {
		$query = $this->query ( $sql, $params );
		try {
			$result = $this->pdoStatement->rowCount ();
		} catch ( PDOException $e ) {
			throw new DBException ( $e->getMessage () );
		}
		return $result;
	}
}