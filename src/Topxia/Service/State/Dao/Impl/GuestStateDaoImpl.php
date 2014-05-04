<?php

namespace Topxia\Service\State\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\State\Dao\GuestStateDao;
use Topxia\Common\DaoException;
use PDO;

class GuestStateDaoImpl extends BaseDao implements GuestStateDao
{
    protected $table = 'guest_state';

    public function getGuestState($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function findGuestStatesByIds(array $ids)
    {
        if(empty($ids)){ return array(); }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function searchGuestStates($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->createGuestStateQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ? : array();
    }

    public function searchGuestStateCount($conditions)
    {
        $builder = $this->createGuestStateQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    private function createGuestStateQueryBuilder($conditions)
    {
        $conditions = array_filter($conditions);
       

        return  $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'state')
                       
            ->andWhere('date = :date');
    }

    public function addGuestState($guestState)
    {
        $affected = $this->getConnection()->insert($this->table, $guestState);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert guestState error.');
        }
        return $this->getGuestState($this->getConnection()->lastInsertId());
    }

    public function updateGuestState($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getGuestState($id);
    }

    public function deleteGuestState($id)
    {
         return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function deleteByDate($date)
    {
         return $this->getConnection()->delete($this->table, array('date' => $date));
    }

    

}