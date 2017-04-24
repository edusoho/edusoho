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
        
        $this->connection->selectCollection($this->tableName)->insert(array(
            'version' => $migration->getVersion()
        ));

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
        $this->connection->selectCollection($this->tableName)->remove(array(
            'version' => $migration->getVersion()
        ));

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
        $this->connection->selectCollection($this->tableName)->ensureIndex(
            'version',
            array('unique' => 1)
        );
        return $this;
    }
}

