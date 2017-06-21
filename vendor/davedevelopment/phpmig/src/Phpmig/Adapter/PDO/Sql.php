<?php

namespace Phpmig\Adapter\PDO;

use Phpmig\Migration\Migration,
    Phpmig\Adapter\AdapterInterface,
    PDO;

/**
 * Simple PDO adapter to work with SQL database
 *
 * @author Samuel Laulhau https://github.com/lalop
 */

class Sql implements AdapterInterface
{

    /**
     * @var \PDO
     */
    protected $connection    = null;

    /**
     * @var string
     */
    protected $tableName     = null;

    /**
     * @var string
     */
    protected $pdoDriverName = null;

    /**
     * Constructor
     *
     * @param \PDO $connection
     * @param string $tableName
     */
    public function __construct(\PDO $connection, $tableName)
    {
        $this->connection    = $connection;
        $this->tableName     = $tableName;
        $this->pdoDriverName = $connection->getAttribute(\PDO::ATTR_DRIVER_NAME);
    }

    private function quotedTableName()
    {
        return "`{$this->tableName}`";
    }

    /**
     * Fetch all
     *
     * @return array
     */
    public function fetchAll()
    {
        $sql = $this->getQuery('fetchAll');

        return $this->connection->query($sql, PDO::FETCH_COLUMN, 0)->fetchAll();
    }

    /**
     * Up
     *
     * @param Migration $migration
     * @return self
     */
    public function up(Migration $migration)
    {
        $sql = $this->getQuery('up');

        $this->connection->prepare($sql)
                ->execute(array(':version' => $migration->getVersion()));

        return $this;
    }

    /**
     * Down
     *
     * @param Migration $migration
     * @return self
     */
    public function down(Migration $migration)
    {
        $sql = $this->getQuery('down');

        $this->connection->prepare($sql)
                ->execute(array(':version' => $migration->getVersion()));

        return $this;
    }


    /**
     * Is the schema ready?
     *
     * @return bool
     */
    public function hasSchema()
    {
        $sql = $this->getQuery('hasSchema');

        $tables = $this->connection->query($sql);

        while($table = $tables->fetchColumn()) {
            if ($table == $this->tableName) {
                return true;
            }
        }

        return false;
    }


    /**
     * Create Schema
     *
     * @return DBAL
     */
    public function createSchema()
    {
        $sql = $this->getQuery('createSchema');

        $this->connection->exec($sql);

        return $this;
    }

    /**
     * Get the appropriate query for the PDO driver
     *
     * At present, only queries for sqlite, mysql, & pgsql are specified; if a
     * different PDO driver is used, the mysql/pgsql queries will be returned,
     * which may or may not work for the given database.
     *
     * @param string $type
     * The type of the query to retrieve
     *
     * @return string
     */
    protected function getQuery($type)
    {
        $queries = array();

        switch($this->pdoDriverName)
        {
            case 'dblib':
            case 'sqlsrv':
                $queries = array(

                        'fetchAll'     => "SELECT version FROM {$this->tableName} ORDER BY version ASC",

                        'up'           => "INSERT INTO {$this->tableName} VALUES (:version)",

                        'down'         => "DELETE FROM {$this->tableName} WHERE version = :version",

                        'hasSchema'    => "Select Table_name as 'Table name'
                            From Information_schema.Tables
                            Where Table_type = 'BASE TABLE' and Objectproperty
                            (Object_id(Table_name), 'IsMsShipped') = 0",

                        'createSchema' => "CREATE table {$this->tableName} (version varchar(255) NOT NULL)",

                    );
                break;

            case 'sqlite':
                $queries = array(

                        'fetchAll'     => "SELECT `version` FROM {$this->quotedTableName()} ORDER BY `version` ASC",

                        'up'           => "INSERT INTO {$this->quotedTableName()} VALUES (:version);",

                        'down'         => "DELETE FROM {$this->quotedTableName()} WHERE version = :version",

                        'hasSchema'    => "SELECT `name` FROM `sqlite_master` WHERE `type`='table';",

                        'createSchema' => "CREATE table {$this->quotedTableName()} (`version` NOT NULL);",

                    );
                break;

            case 'pgsql':
                $queries = array(

                        'fetchAll'     => "SELECT \"version\" FROM \"{$this->tableName}\" ORDER BY \"version\" ASC",

                        'up'           => "INSERT INTO \"{$this->tableName}\" VALUES (:version)",

                        'down'         => "DELETE FROM \"{$this->tableName}\" WHERE \"version\" = :version",

                        'hasSchema'    => "SELECT \"tablename\" FROM \"pg_tables\"",

                        'createSchema' => "CREATE TABLE \"{$this->tableName}\" (\"version\" VARCHAR(255) NOT NULL)",

                    );
                break;

            case 'mysql':
            default:
                $queries = array(

                        'fetchAll'     => "SELECT `version` FROM {$this->quotedTableName()} ORDER BY `version` ASC",

                        'up'           => "INSERT into {$this->quotedTableName()} set version = :version",

                        'down'         => "DELETE from {$this->quotedTableName()} where version = :version",

                        'hasSchema'    => "SHOW TABLES;",

                        'createSchema' => "CREATE TABLE {$this->quotedTableName()} (`version` VARCHAR(255) NOT NULL);",

                    );
                break;
        }

        if(!array_key_exists($type, $queries))
            throw new \InvalidArgumentException("Query type not found: '{$type}'");

        return $queries[$type];
    }
}

