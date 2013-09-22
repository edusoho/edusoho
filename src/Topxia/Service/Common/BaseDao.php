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
        $sql = "UPDATE {$this->table} SET ";
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
        return $this->table;
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

}