<?php

namespace Topxia\Service\Access\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Access\Dao\LogDao;
use Topxia\Common\DaoException;
use PDO;

class LogDaoImpl extends BaseDao implements LogDao
{
    protected $table = 'access_log';

    public function getLog($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function findLogsByIds(array $ids)
    {
        if(empty($ids)){ return array(); }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function searchLogs($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->createLogQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ? : array();
    }

    public function searchLogCount($conditions)
    {
        $builder = $this->createLogQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    private function createLogQueryBuilder($conditions)
    {
        $conditions = array_filter($conditions);       

        return  $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'access_log')
            ->andWhere('userId = :userId')

            ->andWhere('guestId = :guestId')
            
            ->andWhere('mTookeen = :mTookeen');
    }

    public function addLog($guest)
    {
        $affected = $this->getConnection()->insert($this->table, $guest);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert guest error.');
        }
        return $this->getLog($this->getConnection()->lastInsertId());
    }

    public function updateLog($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getLog($id);
    }

    public function waveCounterById($id, $name, $number){

    }

    public function clearCounterById($id, $name){

    }

    

}