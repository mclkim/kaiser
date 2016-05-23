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
		try {
			$query = $this->query ( $sql, $params );
			$this->setFetchMode( \PDO::FETCH_CLASS );
			$result = $query->get();
		} catch ( PDOException $e ) {
			throw new DBException ( $e->getMessage () );
		} catch (\Exception $e) {
		}
		return $result;
	}
	function executePreparedQueryOne($sql, $params = array()) {
		logger($this->executeEmulateQuery($sql, $params ));
		try {
			$query = $this->query ( $sql, $params );
			$this->setFetchMode( \PDO::FETCH_OBJ );
			$result = $query->first();
		} catch ( PDOException $e ) {
			throw new DBException ( $e->getMessage () );
		} catch (\Exception $e) {
		}
		return $result;
	}
	function executePreparedQueryToArrayList($sql, $params = array()) {
		logger($this->executeEmulateQuery($sql, $params ));
		try {
			$query = $this->query ( $sql, $params );
			$this->setFetchMode( \PDO::FETCH_NUM );
			$result = $query->get();
		} catch ( PDOException $e ) {
			throw new DBException ( $e->getMessage () );
		} catch (\Exception $e) {
		}
		return $result;
	}
	function executePreparedQueryToColList($sql, $params = array(), $col = 0) {
		logger($this->executeEmulateQuery($sql, $params ));
		try {
			$query = $this->query ( $sql, $params );
			$this->setFetchMode( \PDO::FETCH_COLUMN );
			$result = $query->get();
		} catch ( PDOException $e ) {
			throw new DBException ( $e->getMessage () );
		} catch (\Exception $e) {
		}
		return $result;
	}
	function executePreparedQueryToMap($sql, $params = array()) {
		logger($this->executeEmulateQuery($sql, $params ));
		try {
			$query = $this->query ( $sql, $params );
			$this->setFetchMode( \PDO::FETCH_ASSOC );
			$result = $query->get();
		} catch ( PDOException $e ) {
			throw new DBException ( $e->getMessage () );
		} catch (\Exception $e) {
		}
		return $result;
	}
	function executePreparedQueryToObjList($sql, $params = array()) {
		logger($this->executeEmulateQuery($sql, $params ));
		try {
			$query = $this->query ( $sql, $params );
			$this->setFetchMode( \PDO::FETCH_OBJ );
			$result = $query->get();
		} catch ( PDOException $e ) {
			throw new DBException ( $e->getMessage () );
		} catch (\Exception $e) {
		}
		return $result;
	}
	function executePreparedUpdate($sql, $params = array()) {
		logger($this->executeEmulateQuery($sql, $params ));
		try {
			$query = $this->query ( $sql, $params );
		} catch ( PDOException $e ) {
			throw new DBException ( $e->getMessage () );
		} catch (\Exception $e) {
		}
		return $result;
	}
	function executeTransaction(){
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
}