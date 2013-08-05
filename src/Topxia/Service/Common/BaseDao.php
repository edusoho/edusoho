<?php
namespace Topxia\Service\Common;

use PDO,
    Topxia\Common\DaoException;

abstract class BaseDao
{

    protected $connection;

    protected $table = null;

    protected $primaryKey = 'id';

    protected function fetch($id)
    {
        $builder = $this->createQueryBuilder()
            ->select('*')->from($this->table, $this->table)->where("{$this->primaryKey} = :{$this->primaryKey}")
            ->setMaxResults(1)
            ->setParameter(":{$this->primaryKey}", $id);

        return $builder->execute()->fetch(PDO::FETCH_ASSOC);
    }

    protected function insert ($data)
    {
        $affected = $this->getConnection()->insert($this->table, $data);
        if ($affected <= 0) {
            throw $this->createDaoException('insert error.');
        }
        return $this->getConnection()->lastInsertId();
    }

    protected function update ($id, array $data)
    {
        if (!empty($data)) {
            $affected = $this->getConnection()->update($this->table, $data, array(
                'id' => $id
            ));
        }
        return $this->fetch($id);
    }

    protected function wave ($id, $fields) {
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
    
    protected function delete ($id)
    {
        return $this->getConnection()->delete($this->table, array(
            'id' => $id
        ));
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

    protected function createDaoException($message = null, $code = 0) {
        return new DaoException($message, $code);
    }

    protected function createDynamicQueryBuilder($conditions)
    {
        return new DynamicQueryBuilder($this->getConnection(), $conditions);
    }

    protected function createQueryBuilder()
    {
        return $this->getConnection()->createQueryBuilder();
    }

}