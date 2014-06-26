<?php
namespace Topxia\Service\Common;

use PDO,
    Topxia\Common\DaoException;

abstract class BaseDao
{
    protected $connection;

    protected $table = null;

    protected $primaryKey = 'id';

    private static $cachedObjects = array();

    protected function wave ($id, $fields) 
    {
        $sql = "UPDATE {$this->getTable()} SET ";
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
        if($this->table){
            return $this->table;
        }else{
            return self::TABLENAME;
        }
    }

    public function getConnection ()
    {
        return $this->connection;
    }

    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    protected function createDaoException($message = null, $code = 0) 
    {
        return new DaoException($message, $code);
    }

    protected function createDynamicQueryBuilder($conditions)
    {
        return new DynamicQueryBuilder($this->getConnection(), $conditions);
    }

    protected function createSerializer()
    {
        if (!isset(self::$cachedObjects['field_serializer'])) {
            self::$cachedObjects['field_serializer'] = new FieldSerializer();
        }
        return self::$cachedObjects['field_serializer'];
    }

    protected function filterStartLimit(&$start, &$limit)
    {
       $start = (int) $start;
       $limit = (int) $limit; 
    }

    protected function checkOrderBy (array $orderBy, array $allowedOrderByFields)
    {
        if (empty($orderBy[0]) or empty($orderBy[1])) {
            throw new \RuntimeException('orderBy参数不正确');
        }
        
        if (!in_array($orderBy[0], $allowedOrderByFields)){
            throw new \RuntimeException("不允许对{$orderBy[0]}字段进行排序", 1);
        }
        if (!in_array($orderBy[1], array('ASC','DESC'))){
            throw new \RuntimeException("orderBy排序方式错误", 1);
        }

        return $orderBy;
    }

}