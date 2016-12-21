<?php

namespace Phpmig\Adapter\PDO;

use Phpmig\Migration\Migration,
    Phpmig\Adapter\AdapterInterface,
    PDO;

/**
 * Simple PDO adapter to work with Postgres SQL database in particular.
 * @author Theodson https://github.com/theodson
 */
class SqlPgsql extends Sql
{
    private $quote = "\"";
    private $schemaName = "public";

    /**
     * Constructor
     *
     * @param \PDO $connection
     * @param string $tableName
     */
    public function __construct(\PDO $connection, $tableName, $schemaName = 'public')
    {
        parent::__construct($connection, $tableName);
        $driver = $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME);
        $this->quote = in_array($driver, array('mysql', 'pgsql')) ? '"' : '`';
        $this->schemaName = $schemaName;
    }

    private function quotedTableName()
    {
        $sql = "{$this->quote}{$this->schemaName}{$this->quote}.{$this->quote}{$this->tableName}{$this->quote}";
        return $sql;
    }

    /**
     * Fetch all
     *
     * @return array
     */
    public function fetchAll()
    {
        $sql = "SELECT {$this->quote}version{$this->quote} FROM {$this->quotedTableName()} ORDER BY {$this->quote}version{$this->quote} ASC";
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
        $sql = "INSERT into {$this->quotedTableName()} (version) VALUES (:version);";
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
        $sql = "DELETE from {$this->quotedTableName()} where version = :version";
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
        $tables = $this->connection->query("SELECT table_name FROM information_schema.tables WHERE table_schema = '{$this->schemaName}';");
        while ($table = $tables->fetchColumn()) {
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
        $sql = sprintf("SELECT schema_name FROM {$this->quote}information_schema{$this->quote}.{$this->quote}schemata{$this->quote} WHERE schema_name = '%s';",
            $this->schemaName);
        $pgSchemas = $this->connection->exec($sql);

        if (empty($pgSchemas)) {
            $sql = sprintf("CREATE SCHEMA %s;", $this->schemaName);
            if (FALSE === $this->connection->exec($sql)) {
                $e = $this->connection->errorInfo();
            }
        }

        $sql = "CREATE table {$this->quotedTableName()} (version %s NOT NULL, {$this->quote}migrate_date{$this->quote} timestamp(6) WITH TIME ZONE DEFAULT now())";
        $driver = $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME);
        $sql = sprintf($sql, in_array($driver, array('mysql', 'pgsql')) ? 'VARCHAR(255)' : '');

        if (FALSE === $this->connection->exec($sql)) {
            $e = $this->connection->errorInfo();
        }
        return $this;
    }

}

