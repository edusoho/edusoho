<?php

namespace Topxia\Service\State\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\State\Dao\UserStateDao;
use Topxia\Common\DaoException;
use PDO;

class UserStateDaoImpl extends BaseDao implements UserStateDao
{
    protected $table = 'user_state';

    public function getUserState($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function findUserStatesByIds(array $ids)
    {
        if(empty($ids)){ return array(); }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function searchUserStates($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->createUserStateQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ? : array();
    }

    public function searchUserStateCount($conditions)
    {
        $builder = $this->createUserStateQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    private function createUserStateQueryBuilder($conditions)
    {
        $conditions = array_filter($conditions);
       

        return  $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'state')
                       
            ->andWhere('date = :date');
    }

    public function addUserState($userState)
    {
        $affected = $this->getConnection()->insert($this->table, $userState);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert userState error.');
        }
        return $this->getUserState($this->getConnection()->lastInsertId());
    }

    public function updateUserState($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getUserState($id);
    }

    public function deleteUserState($id)
    {
         return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function deleteByDate($date)
    {
         return $this->getConnection()->delete($this->table, array('date' => $date));
    }

    

}