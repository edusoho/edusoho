<?php

namespace Phpmig\Adapter;

use Phpmig\Migration\Migration,
    Phpmig\Adapter\AdapterInterface;

/**
 * @author Samuel Laulhau https://github.com/lalop
 */

class Mongo implements AdapterInterface
{

    /**
     * @var \MongoDb
     */
    protected $connection    = null;

    /**
     * @var string
     */
    protected $tableName     = null;

    /**
     * Constructor
     *
     * @param \MongoDb $connection
     * @param string $tableName
     */
    public function __construct(\MongoDb $connection, $tableName)
    {
        $this->connection    = $connection;
        $this->tableName     = $tableName;
    }

    /**
     * Fetch all
     *
     * @return array
     */
    public function fetchAll()
    {
        $cursor = $this->connection->selectCollection($this->tableName)->find();
        $versions = array();
        foreach($cursor as $version) $versions[] = $version['version'];
        return $versions;
    }

    /**
     * Up
     *
     * @param Migration $migration
     * @return self
     */
    public function up(Migration $migration)
    {
        $document = array('version' => $migration->getVersion());
        $this->connection->selectCollection($this->tableName)->insert($document);

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
        $document = array('version' => $migration->getVersion());
        $this->connection->selectCollection($this->tableName)->remove($document);

        return $this;
    }


    /**
     * Is the schema ready?
     *
     * @return bool
     */
    public function hasSchema()
    {
        $tableName = $this->tableName;
        return array_filter(
            $this->connection->getCollectionNames(),
            function( $collection ) use ($tableName) {
                return $collection === $tableName;
        });
    }


    /**
     * Create Schema
     *
     * @return DBAL
     */
    public function createSchema()
    {
        $keys = 'version';
        $options = array('unique' => 1);
        $this->connection->selectCollection($this->tableName)->ensureIndex($keys, $options);

        return $this;
    }
}

