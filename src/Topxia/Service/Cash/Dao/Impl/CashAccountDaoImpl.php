<?php

namespace Topxia\Service\Cash\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Cash\Dao\CashAccountDao;

class CashAccountDaoImpl extends BaseDao implements CashAccountDao
{
    protected $table = 'cash_account';

    public function getAccount($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function getAccountByUserId($userId, $lock = false)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? LIMIT 1" . ($lock ? ' FOR UPDATE' : '');
        return $this->getConnection()->fetchAssoc($sql, array($userId)) ? : null;
    }

    public function findAccountsByUserIds($userIds)
    {
        if(empty($userIds)) { 
            return array(); 
        }
        $marks = str_repeat('?,', count($userIds) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE userId IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $userIds);
    }

    public function addAccount($fields)
    {
        $affected = $this->getConnection()->insert($this->table, $fields);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert cash account error.');
        }
        return $this->getAccount($this->getConnection()->lastInsertId());
    }

    public function updateAccount($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getAccount($id);
    }

    public function waveCashField($id, $value)
    {
        $sql = "UPDATE {$this->table} SET cash = cash + ? WHERE id = ? LIMIT 1";
        
        return $this->getConnection()->executeQuery($sql, array($value, $id));
    }

    public function waveDownCashField($id, $value)
    {
        $sql = "UPDATE {$this->table} SET cash = cash - ? WHERE id = ? LIMIT 1";
        
        return $this->getConnection()->executeQuery($sql, array($value, $id));
    }

    public function searchAccount($conditions, $orderBy, $start, $limit)
    {
        $builder = $this->createAccountQueryBuilder($conditions)
            ->select('*')
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ? : array();
    }

    public function searchAccountCount($conditions)
    {
        $builder = $this->createAccountQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    private function createAccountQueryBuilder($conditions)
    {

        $conditions = array_filter($conditions);
        return $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'cash_account')
            ->andWhere('userId = :userId');
    }

}