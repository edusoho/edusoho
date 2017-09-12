<?php
/**
 * @package    Phpmig
 * @subpackage Phpmig\Adapter
 */
namespace Phpmig\Adapter\Zend;

use Phpmig\Adapter\AdapterInterface;
use Phpmig\Migration\Migration;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Metadata\Metadata;
use Zend\Db\Sql\Ddl\Column\Varchar;
use Zend\Db\Sql\Ddl\CreateTable;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\TableGateway;

/**
 * Phpmig adapter for zendframework/zend-db
 *
 * @package Phpmig\Adapter\Zend
 */
class DbAdapter implements AdapterInterface
{
    /**
     * @var TableGateway
     */
    private $tableGateway;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var Adapter
     */
    private $adapter;

    /**
     * @param Adapter $adapter
     * @param string $tableName
     */
    public function __construct(Adapter $adapter, $tableName)
    {
        $this->adapter   = $adapter;
        $this->tableName = $tableName;
    }

    /**
     * @return TableGateway
     */
    private function tableGateway()
    {
        if (!$this->tableGateway) {
            $this->tableGateway = new TableGateway($this->tableName, $this->adapter);
        }

        return $this->tableGateway;
    }

    /**
     * Get all migrated version numbers
     *
     * @return array
     */
    public function fetchAll()
    {
        $result = $this->tableGateway()->select(function (Select $select) {
            $select->order('version ASC');
        })->toArray();

        // imitate fetchCol
        return array_map(function ($item) {
            return $item['version'];
        }, $result);
    }

    /**
     * Up
     *
     * @param Migration $migration
     * @return AdapterInterface
     */
    public function up(Migration $migration)
    {
        $this->tableGateway()->insert(['version' => $migration->getVersion()]);
        return $this;
    }

    /**
     * Down
     *
     * @param Migration $migration
     * @return AdapterInterface
     */
    public function down(Migration $migration)
    {
        $this->tableGateway()->delete(['version' => $migration->getVersion()]);
        return $this;
    }

    /**
     * Is the schema ready?
     *
     * @return bool
     */
    public function hasSchema()
    {
        try {
            $metadata = new Metadata($this->adapter);
            $metadata->getTable($this->tableName);
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * Create Schema
     *
     * @return AdapterInterface
     */
    public function createSchema()
    {
        $ddl = new CreateTable($this->tableName);
        $ddl->addColumn(new Varchar('version', 255));

        $sql = new Sql($this->adapter);

        $this->adapter->query(
            $sql->buildSqlString($ddl),
            Adapter::QUERY_MODE_EXECUTE
        );

        return $this;
    }
}
