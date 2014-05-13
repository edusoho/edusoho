<?php

namespace Topxia\Service\State\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\State\Dao\PartnerStateDao;
use Topxia\Common\DaoException;
use PDO;

class PartnerStateDaoImpl extends BaseDao implements PartnerStateDao
{
    protected $table = 'partner_state';

    public function getPartnerState($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function findPartnerStatesByIds(array $ids)
    {
        if(empty($ids)){ return array(); }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function searchPartnerStates($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->createPartnerStateQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ? : array();
    }

    public function searchPartnerStateCount($conditions)
    {
        $builder = $this->createPartnerStateQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    private function createPartnerStateQueryBuilder($conditions)
    {
        $conditions = array_filter($conditions);
       

        return  $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'state')

            ->andWhere('partnerId = :partnerId')
                       
            ->andWhere('date >= :date1')

            ->andWhere('date <= :date2');
    }

    public function addPartnerState($partnerState)
    {
        $affected = $this->getConnection()->insert($this->table, $partnerState);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert partnerState error.');
        }
        return $this->getPartnerState($this->getConnection()->lastInsertId());
    }

    public function updatePartnerState($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getPartnerState($id);
    }

    public function deletePartnerState($id)
    {
         return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function deleteByDate($date,$partnerId)
    {
         return $this->getConnection()->delete($this->table, array('date' => $date,'partnerId'=>$partnerId));
    }
    

}