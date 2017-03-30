<?php
namespace Topxia\Service\Common;

use Topxia\Service\Common\FieldChecker;

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

    protected function waveCache()
    {
        $args     = func_get_args();
        $callback = array_pop($args);
        $key      = $this->table.':'.array_shift($args);
        $redis    = $this->getRedis();

        if ($redis) {
            $currentTime = time();
            $data        = $redis->get($key);

            if ($data) {
                if ($currentTime - $data['syncTime'] > 600) {
                    $args[2] += $data['increment'];
                    call_user_func_array($callback, $args);
                    $redis->setex($key, 30 * 60 * 60, array('increment' => 0, 'syncTime' => $currentTime));
                } else {
                    $data['increment'] += $args[2];
                    $redis->setex($key, 30 * 60 * 60, array('increment' => $data['increment'], 'syncTime' => $data['syncTime']));
                }
            } else {
                $redis->setex($key, 30 * 60 * 60, array('increment' => $args[2], 'syncTime' => $currentTime));
            }
        } else {
            call_user_func_array($callback, $args);
        }
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
        return $this;
    }

    public function setConnectionFactory($connectionFactory)
    {
        $this->connectionFactory = $connectionFactory;
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

        $key = $this->getPrefixKey().':'.array_shift($args);

        if (isset($this->dataCached[$key])) {
            return $this->dataCached[$key];
        }

        $redis = $this->getRedis();

        if ($redis) {
            $data = $redis->get($key);
            if ($data !== false) {
                $this->dataCached[$key] = $data;
                return $data;
            }
        }
        if ($this->isRunByCommand()){
            return call_user_func_array($callback, $args);
        }else{
            $this->dataCached[$key] = call_user_func_array($callback, $args);

            if ($redis) {
                $redis->setex($key, 2 * 60 * 60, $this->dataCached[$key]);
            }
            return $this->dataCached[$key];
        }
        
    }

    private function  isRunByCommand()
    {
       return  getenv('IS_RUN_BY_COMMAND') && getenv('IS_RUN_BY_COMMAND') === 'true';
    }

    protected function getCacheVersion($key)
    {
        $redis = $this->getRedis();

        if (!$redis) {
            return 0;
        }

        $version = 0;

        if (isset($this->dataCached[$key])) {
            $version = $this->dataCached[$key];
        }

        if ($version == 0) {
            $version = $redis->get($key);

            if (!$version) {
                $version = 1;
                $redis->incrBy($key, $version);
            }

            $this->dataCached[$key] = $version;
        }

        return $version;
    }

    protected function incrVersions($keys)
    {
        $redis = $this->getRedis();

        if ($redis) {
            foreach ($keys as $key) {
                $redis->incr($key);
                unset($this->dataCached[$key]);
            }
        } else {
            unset($this->dataCached);
        }
    }

    protected function getTableVersion()
    {
        $key = "{$this->table}:version";
        return $this->getCacheVersion($key);
    }

    protected function clearCached()
    {
        $key = "{$this->table}:version";
        $this->incrVersions(array($key));
        $this->dataCached = array();
    }

    protected function deleteCache($keys)
    {
        if (empty($keys)) {
            return;
        }

        $deleteKeys = array();
        foreach ($keys as $key => $value) {
            $deleteKeys[] = $this->getPrefixKey().':'.$value;
        }

        $redis = $this->getRedis();

        if ($redis) {
            foreach ($deleteKeys as $key) {
                $redis->delete($key);
            }
        }

        foreach ($deleteKeys as $key) {
            unset($this->dataCached[$key]);
        }
    }

    /**
     * @deprecated this is deprecated and will be removed. Please use use `throw new Topxia\Common\Exception\XXXException(...)` instead.
     */
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

    protected function validateOrderBy(array $orderBy, $allowedOrderByFields = array())
    {
        $keys = array_keys($orderBy);

        foreach ($orderBy as $field => $order) {
            FieldChecker::checkFieldName($field);
            if (!in_array($field, $allowedOrderByFields)) {
                throw new \RuntimeException($this->getKernel()->trans('不允许对%field%字段进行排序', array('%field%' => $field)), 1);
            }

            if (!in_array($order, array('ASC', 'DESC'))) {
                throw new \RuntimeException($this->getKernel()->trans('orderBy排序方式错误'), 1);
            }
        }
    }

    protected function getKernel()
    {
        return ServiceKernel::instance();
    }

    protected function checkOrderBy(array $orderBy, array $allowedOrderByFields = array())
    {
        if (empty($orderBy)) {
            throw new \RuntimeException($this->getKernel()->trans('orderBy参数不正确'));
        }

        for ($i = 0; $i < count($orderBy); $i = $i + 2) {
            if (empty($orderBy[$i]) || empty($orderBy[$i + 1])) {
                throw new \RuntimeException($this->getKernel()->trans('orderBy参数不正确'));
            }

            $field = $orderBy[$i];
            $seq = $orderBy[$i + 1];

            FieldChecker::checkFieldName($field);

            if (!empty($allowedOrderByFields)  && !in_array($field, $allowedOrderByFields)) {
                throw new \RuntimeException($this->getKernel()->trans('不允许对%orderBy%字段进行排序', array('%orderBy%' => $field)), 1);
            }

            if (!in_array(strtoupper($seq), array('ASC', 'DESC'))) {
                throw new \RuntimeException($this->getKernel()->trans('orderBy排序方式错误'), 1);
            }
        }

        return $orderBy;
    }

    protected function getPrefixKey()
    {
        return "{$this->table}:v{$this->getTableVersion()}";
    }

    protected function hasEmptyInCondition($conditions, $fields)
    {
        foreach ($conditions as $key => $condition) {
            if (in_array($key, $fields) && empty($condition)) {
                return true;
            }
        }
        return false;
    }

    protected function generateKeyWhenSearch($conditions, $orderBy, $start, $limit)
    {
        $version = $this->getCacheVersion("{$this->table}:search");
        $keys = 'search:'.$version;

        if(!empty($conditions)) {
            ksort($conditions);
            foreach ($conditions as $key => $value) {
                if(is_array($value)) {
                    $keys .= ":{$key}:".implode('-', $value);
                } else {
                    $keys .= ":{$key}:{$value}";
                }
            }
        }
        return "{$keys}:{$orderBy[0]}:{$orderBy[1]}:start:{$start}:limit:{$limit}";
    }

    protected function generateKeyWhenCount($conditions)
    {
        $version = $this->getCacheVersion("{$this->table}:search");
        $keys = "count:{$version}";
        foreach ($conditions as $key => $value) {
            if(is_array($value)) {
                $keys .= ":{$key}:".implode('-', $value);
            } else {
                $keys .= ":{$key}:{$value}";
            }
        }
        return $keys;
    }
}
