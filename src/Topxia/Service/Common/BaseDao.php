<?php
namespace Topxia\Service\Common;

use Topxia\Common\DaoException;

abstract class BaseDao
{
    protected $connection;

    protected $table = null;

    protected $primaryKey = 'id';

    private static $cachedSerializer = array();

    protected $dataCache = array();

    protected $redis;

    public function wave($id, $fields)
    {
        $sql        = "UPDATE {$this->getTable()} SET ";
        $fieldStmts = array();

        foreach (array_keys($fields) as $field) {
            $fieldStmts[] = "{$field} = {$field} + ? ";
        }

        $sql .= join(',', $fieldStmts);
        $sql .= "WHERE id = ?";

        $params = array_merge(array_values($fields), array($id));
        return $this->getConnection()->executeUpdate($sql, $params);
    }

    public function getTable()
    {
        if ($this->table) {
            return $this->table;
        } else {
            return self::TABLENAME;
        }
    }

    /**
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    public function getRedis()
    {
        return $this->redis;
    }

    public function setRedis($redis)
    {
        $this->redis = $redis;
    }

    protected function fetchCached()
    {
        $args     = func_get_args();
        $callback = array_pop($args);

        $key = "{$this->table}:v{$this->getTableVersion()}:".array_shift($args);

        if (isset($this->dataCached[$key])) {
            return $this->dataCached[$key];
        }

        $redis = $this->getRedis();

        if ($redis) {
            $data = $redis->get($key);

            if ($data) {
                $this->dataCached[$key] = $data;
                return $data;
            }
        }

        $this->dataCached[$key] = call_user_func_array($callback, $args);

        if ($redis) {
            $redis->setex($key, 2 * 60 * 60, $this->dataCached[$key]);
        }

        return $this->dataCached[$key];
    }

    protected function getTableVersion()
    {
        $redis = $this->getRedis();

        if (!$redis) {
            return 0;
        }

        $version = 0;

        if (isset($this->dataCached['version'])) {
            $version = $this->dataCached['version'];
        }

        if ($version == 0) {
            $version = $redis->get("{$this->table}:version");

            if (!$version) {
                $version = 1;
                $redis->incrBy("{$this->table}:version", $version);
            }

            $this->dataCached["version"] = $version;
        }

        return $version;
    }

    protected function clearCached()
    {
        $this->dataCached = array();

        $redis = $this->getRedis();

        if ($redis) {
            $redis->incr("{$this->table}:version");
        }
    }

    protected function createDaoException($message = null, $code = 0)
    {
        return new DaoException($message, $code);
    }

    protected function createDynamicQueryBuilder($conditions)
    {
        return new DynamicQueryBuilder($this->getConnection(), $conditions);
    }

    public function createSerializer()
    {
        if (!isset(self::$cachedSerializer['field_serializer'])) {
            self::$cachedSerializer['field_serializer'] = new FieldSerializer();
        }

        return self::$cachedSerializer['field_serializer'];
    }

    protected function filterStartLimit(&$start, &$limit)
    {
        $start = (int) $start;
        $limit = (int) $limit;
    }

    protected function addOrderBy($builder, $orderBy)
    {
        foreach ($orderBy as $column => $order) {
            if (in_array($column, array('createdTime', 'ups')) && in_array($order, array('DESC', 'ASC'))) {
                $builder->addOrderBy($column, $order);
            }
        }

        return $builder;
    }

    protected function validateOrderBy(array $orderBy, $allowedOrderByFields)
    {
        $keys = array_keys($orderBy);

        foreach ($orderBy as $field => $order) {
            if (!in_array($field, $allowedOrderByFields)) {
                throw new \RuntimeException("不允许对{$field}字段进行排序", 1);
            }

            if (!in_array($order, array('ASC', 'DESC'))) {
                throw new \RuntimeException("orderBy排序方式错误", 1);
            }
        }
    }

    protected function checkOrderBy(array $orderBy, array $allowedOrderByFields)
    {
        if (empty($orderBy[0]) || empty($orderBy[1])) {
            throw new \RuntimeException('orderBy参数不正确');
        }

        if (!in_array($orderBy[0], $allowedOrderByFields)) {
            throw new \RuntimeException("不允许对{$orderBy[0]}字段进行排序", 1);
        }

        if (!in_array(strtoupper($orderBy[1]), array('ASC', 'DESC'))) {
            throw new \RuntimeException("orderBy排序方式错误", 1);
        }

        return $orderBy;
    }

    protected function hasEmptyInCondition($conditions, $fields)
    {
        foreach ($conditions as $key => $condition) {
            if(in_array($key, $fields) && empty($condition)) {
                return true;
            }
        }
        return false;
    }
}
