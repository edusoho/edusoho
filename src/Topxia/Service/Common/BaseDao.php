<?php
namespace Topxia\Service\Common;

use PDO,
    Topxia\Common\DaoException;

abstract class BaseDao
{
    protected $connection;

    protected $table = null;

    protected $primaryKey = 'id';

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

    protected function filterStartLimit(&$start, &$limit)
    {
       $start = (int) $start;
       $limit = (int) $limit; 
    }

    protected function checkOrderByField (array $orderBy, array $allowedOrderByFields)
    {
        if (count($orderBy) != 2) {
            throw new \Exception("参数错误", 1);
        }

        $orderBy = array_values($orderBy);
        if (!in_array($orderBy[0], $allowedOrderByFields)){
            throw new \Exception("参数错误", 1);
        }
        if (!in_array($orderBy[1], array('ASC','DESC'))){
            throw new \Exception("参数错误", 1);
        }

        return $orderBy;
    }

}