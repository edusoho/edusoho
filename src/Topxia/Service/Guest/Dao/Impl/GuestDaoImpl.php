<?php

namespace Topxia\Service\Guest\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Guest\Dao\GuestDao;
use Topxia\Common\DaoException;
use PDO;

class GuestDaoImpl extends BaseDao implements GuestDao
{
    protected $table = 'guest';

    public function getGuest($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function findGuestByUserId($userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($userId));
    }

    public function findGuestsByIds(array $ids)
    {
        if(empty($ids)){ return array(); }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function searchGuests($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->createGuestQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ? : array();
    }

    public function searchGuestCount($conditions)
    {
        $builder = $this->createGuestQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    private function createGuestQueryBuilder($conditions)
    {
        $conditions = array_filter($conditions);
       

        return  $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'guest')
            ->andWhere('userId = :userId')

            ->andWhere('createdmTookeen = :createdmTookeen')
            
            ->andWhere('loginmTookeen = :loginmTookeen');
    }

    public function addGuest($guest)
    {
        $affected = $this->getConnection()->insert($this->table, $guest);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert guest error.');
        }
        return $this->getGuest($this->getConnection()->lastInsertId());
    }

    public function updateGuest($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getGuest($id);
    }

    

}