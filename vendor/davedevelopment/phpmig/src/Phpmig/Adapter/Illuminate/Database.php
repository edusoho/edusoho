<?php
/**
 * @package    Phpmig
 * @subpackage Phpmig\Adapter
 */
namespace Phpmig\Adapter\Illuminate;

use PDO;
use \Phpmig\Migration\Migration,
    \Phpmig\Adapter\AdapterInterface;
use RuntimeException;

/**
 * @author Andrew Smith http://github.com/silentworks
 */
class Database implements AdapterInterface
{
    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var \Illuminate\Database\Capsule\Manager
     */
    protected $adapter;

    public function __construct($adapter, $tableName)
    {
        $this->adapter = $adapter;
        $this->tableName = $tableName;
    }

    /**
     * Get all migrated version numbers
     *
     * @return array
     */
    public function fetchAll()
    {
        $fetchMode = $this->adapter->connection()
            ->getFetchMode();

        $all = $this->adapter->connection()
            ->table($this->tableName)
            ->orderBy('version')
            ->get();

        return array_map(function($v) use($fetchMode) {

            switch ($fetchMode) {

                case PDO::FETCH_OBJ:
                    return $v->version;

                case PDO::FETCH_ASSOC:
                    return $v['version'];

                default:
                    throw new RuntimeException("The PDO::FETCH_* constant {$fetchMode} is not supported");
            }
        }, $all);
    }

    /**
     * Up
     *
     * @param Migration $migration
     * @return AdapterInterface
     */
    public function up(Migration $migration)
    {
        $this->adapter->connection()
            ->table($this->tableName)
            ->insert(array(
                'version' => $migration->getVersion()
            ));

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
        $this->adapter->connection()
            ->table($this->tableName)
            ->where('version', $migration->getVersion())
            ->delete();

        return $this;
    }

    /**
     * Is the schema ready?
     *
     * @return bool
     */
    public function hasSchema()
    {
        return $this->adapter->schema()->hasTable($this->tableName);
    }

    /**
     * Create Schema
     *
     * @return AdapterInterface
     */
    public function createSchema()
    {
        /* @var \Illuminate\Database\Schema\Blueprint $table */
        $this->adapter->schema()->create($this->tableName, function ($table) {
            $table->string('version');
        });
    }
}