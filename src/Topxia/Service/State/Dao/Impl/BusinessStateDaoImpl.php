<?php

namespace Topxia\Service\State\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\State\Dao\BusinessStateDao;
use Topxia\Common\DaoException;
use PDO;

class BusinessStateDaoImpl extends BaseDao implements BusinessStateDao
{
    protected $table = 'business_state';

    public function getBusinessState($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function findBusinessStatesByIds(array $ids)
    {
        if(empty($ids)){ return array(); }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function searchBusinessStates($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->createBusinessStateQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ? : array();
    }

    public function searchBusinessStateCount($conditions)
    {
        $builder = $this->createBusinessStateQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    private function createBusinessStateQueryBuilder($conditions)
    {
        $conditions = array_filter($conditions);

        if (isset($conditions['prodName'])) {
            $conditions['prodNameLike'] = "%{$conditions['prodName']}%";
            unset($conditions['prodName']);
        }

           
        return  $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'state')

            ->andWhere('prodType = :prodType')

            ->andWhere('prodName like :prodNameLike')

            ->andWhere('prodId = :prodId')

            ->andWhere('priceType = :priceType')

            ->andWhere('orders >= :orders')

            ->andWhere('feeOrders >= :feeOrders')

            ->andWhere('totalFeeOrders >= :totalFeeOrders')
                       
            ->andWhere('date >= :date1')

             ->andWhere('date <= :date2');
    }

    public function addBusinessState($businessState)
    {
        $affected = $this->getConnection()->insert($this->table, $businessState);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert businessState error.');
        }
        return $this->getBusinessState($this->getConnection()->lastInsertId());
    }

    public function updateBusinessState($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getBusinessState($id);
    }

    public function deleteBusinessState($id)
    {
         return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function deleteByDate($date,$prodType,$prodId)
    {
         return $this->getConnection()->delete($this->table, array('date' => $date,'prodType'=>$prodType,'prodId'=>$prodId));
    }

    

}