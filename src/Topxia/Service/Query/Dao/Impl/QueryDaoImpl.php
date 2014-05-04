<?php

namespace Topxia\Service\Query\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Query\Dao\QueryDao;
use Topxia\Common\DaoException;
use PDO;

class QueryDaoImpl extends BaseDao implements QueryDao
{
    protected $table = 'query';

    public function getQuery($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function findQuerysByIds(array $ids)
    {
        if(empty($ids)){ return array(); }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function searchQuerys($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->createQueryQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ? : array();
    }

    public function searchQueryCount($conditions)
    {
        $builder = $this->createQueryQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    private function createQueryQueryBuilder($conditions)
    {
        $conditions = array_filter($conditions);
       
        return  $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'guest')
            ->andWhere('userId = :userId')

            ->andWhere('createdmTookeen = :createdmTookeen')
            
            ->andWhere('lastAccessmTookeen = :lastAccessmTookeen');
    }

    public function addQuery($guest)
    {
        $affected = $this->getConnection()->insert($this->table, $guest);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert guest error.');
        }
        return $this->getQuery($this->getConnection()->lastInsertId());
    }

    public function updateQuery($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getQuery($id);
    }

    

}