<?php

namespace Phpmig\Adapter;

use MongoDB\Database;
use Phpmig\Migration\Migration;

/**
 * @author Carlos Barrero https://github.com/Zeyckler
 */
class MongoDb implements AdapterInterface
{

    /**
     * @var Database
     */
    protected $connection = null;

    /**
     * @var string
     */
    protected $tableName = null;

    /**
     * @param Database $connection
     * @param string   $tableName
     */
    public function __construct(Database $connection, $tableName)
    {
        $this->connection = $connection;
        $this->tableName = $tableName;
    }

    /**
     * Fetch all
     *
     * @return array
     */
    public function fetchAll()
    {
        $cursor = $this->connection->selectCollection($this->tableName)->find(
            array(),
            array('$project' => array('version' => 1))
        );

        return array_map(
            function ($document) {
                return $document['version'];
            },
            $cursor->toArray()
        );
    }

    /**
     * Up
     *
     * @param Migration $migration
     *
     * @return AdapterInterface
     */
    public function up(Migration $migration)
    {
        $document = array('version' => $migration->getVersion());
        $this->connection->selectCollection($this->tableName)->insertOne($document);

        return $this;
    }

    /**
     * Down
     *
     * @param Migration $migration
     *
     * @return AdapterInterface
     */
    public function down(Migration $migration)
    {
        $document = array('version' => $migration->getVersion());
        $this->connection->selectCollection($this->tableName)->deleteOne($document);

        return $this;
    }


    /**
     * Is the schema ready?
     *
     * @return bool
     */
    public function hasSchema()
    {
        foreach ($this->connection->listCollections() as $collection) {
            if ($collection->getName() === $this->tableName) {
                return true;
            }
        }

        return false;
    }


    /**
     * Create Schema
     *
     * @return AdapterInterface
     */
    public function createSchema()
    {
        $key = array('version' => 1);
        $options = array('unique' => true);
        $this->connection->selectCollection($this->tableName)->createIndex($key, $options);

        return $this;
    }
}
