<?php

namespace Phpmig\Adapter\PDO;

use Phpmig\Migration\Migration,
	PDO;

/**
 * Simple PDO adapter to work with ORACLE database in particular.
 * @author omenrpg https://github.com/omenrpg
 */
class SqlOci extends Sql {

	/**
	 * Constructor
	 *
	 * @param \PDO $connection
	 * @param string $tableName
	 *
	 * @throws \Exception
	 */
	public function __construct( \PDO $connection, $tableName ) {
		parent::__construct( $connection, $tableName );
		$driver = $this->connection->getAttribute( PDO::ATTR_DRIVER_NAME );
		if ( ! in_array( $driver, array( 'oci', 'oci8' ) ) ) {
			throw new \Exception( 'Please install OCI drivers for PDO!' );
		}
	}

	/**
	 * Fetch all
	 *
	 * @return array
	 */
	public function fetchAll() {
		$sql = 'SELECT "version" FROM "' . $this->tableName . '" ORDER BY "version" ASC';

		return $this->connection->query( $sql, PDO::FETCH_COLUMN, 0 )->fetchAll();
	}

	/**
	 * Up
	 *
	 * @param Migration $migration
	 *
	 * @return self
	 */
	public function up( Migration $migration ) {
		$sql         = 'INSERT INTO "' . $this->tableName . '" ("version") VALUES (:version)';
		$this->connection->prepare( $sql )
		                 ->execute( array(
			                 ':version'      => $migration->getVersion()
		                 ) );

		return $this;
	}

	/**
	 * Down
	 *
	 * @param Migration $migration
	 *
	 * @return self
	 */
	public function down( Migration $migration ) {
		$sql = 'DELETE from "' . $this->tableName . '" where "version" = :version';
		$this->connection->prepare( $sql )
		                 ->execute( array( ':version' => $migration->getVersion() ) );

		return $this;
	}


	/**
	 * Does migration table exist in db?
	 *
	 * @return bool
	 */
	public function hasSchema() {
		$sql    = 'SELECT count(*) FROM user_tables WHERE table_name = :tableName';
		$sth = $this->connection->prepare( $sql );
		$sth->execute( array(
			':tableName' => $this->tableName
		) );

		if ($sth->fetchColumn() == 0) {
			return false;
		}

		return true;
	}


	/**
	 * Create Schema
	 *
	 * @return self
	 */
	public function createSchema() {
		$sql = 'CREATE table "' . $this->tableName . '" ("version" VARCHAR2(4000) NOT NULL, "migrate_date" TIMESTAMP DEFAULT CURRENT_TIMESTAMP)';
		$this->connection->exec( $sql );

		return $this;
	}

}
