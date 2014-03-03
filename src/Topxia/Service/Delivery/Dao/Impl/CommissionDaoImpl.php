<?php
namespace Topxia\Service\Delivery\Dao\Impl;

use Topxia\Service\Common\BaseDao;

use Topxia\Service\Delivery\Dao\CommissionDao;

class CommissionDaoImpl extends BaseDao implements CommissionDao
{

    protected $table = 'delivery_commission';

    public function getCommission($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function findCommissionsByIds(array $ids)
    {
        if(empty($ids)){
            return array();
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }


    public function searchCommissions($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ? : array(); 
    }

    public function searchCommissionCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function addCommission($commission)
    {
        $affected = $this->getConnection()->insert($this->table, $commission);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert  mysale error.');
        }
        return $this->getCommission($this->getConnection()->lastInsertId());
    }

    public function updateCommission($id, $commission)
    {
        $this->getConnection()->update($this->table, $commission, array('id' => $id));
        return $this->getCommission($id);
    }

    public function deleteCommission($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function getCommissionsByOrder($order)
    {
        $sql = "SELECT * FROM {$this->table} WHERE orderId = ? and orderSn = ? ";
        return $this->getConnection()->fetchAll($sql, array($order['id'],$order['sn']));
    }

    public function computeMyCommissionsOfYesterday($partnerId)
    {
        $sql = "SELECT sum(commission)  as commissions FROM {$this->table} WHERE status='paid' and  salerId = ? and paidTime > ".strtotime('yesterday')." and paidTime < ".strtotime('today');
       return $this->getConnection()->fetchAssoc($sql, array($partnerId));
    }


    public function computeMyCommissionsOfMonth($partnerId)
    {
        $sql = "SELECT sum(commission)  as commissions FROM {$this->table} WHERE status='paid' and  salerId = ? and paidTime > ".strtotime('first day of this month midnight')." and paidTime < ".strtotime('first day of next month midnight');
       return $this->getConnection()->fetchAssoc($sql, array($partnerId));
    }

    public function computeMyCommissionsOfLast($partnerId)
    {
        $sql = "SELECT sum(commission)  as commissions FROM {$this->table} WHERE status='paid' and  salerId = ? and paidTime > ".strtotime('first day of last month midnight')." and paidTime < ".strtotime('first day of this month midnight');
       return $this->getConnection()->fetchAssoc($sql, array($partnerId));
    }

    public function computeMyCommissions($partnerId)
    {
        $sql = "SELECT sum(commission)  as commissions FROM {$this->table} WHERE status='paid' and  salerId = ? ";
       return $this->getConnection()->fetchAssoc($sql, array($partnerId));
    }


    private function _createSearchQueryBuilder($conditions)
    {

        if (isset($conditions['orderSn'])) {
            $conditions['orderSnLike'] = "%{$conditions['orderSn']}%";
            unset($conditions['orderSn']);
        }

        if (isset($conditions['saleTookeen'])) {
            $conditions['saleTookeenLike'] = "%{$conditions['saleTookeen']}%";
            unset($conditions['saleTookeen']);
        }

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from(self::TABLENAME, 'mysale_commission')
            ->andWhere('saleType = :saleType')
            ->andWhere('saleId = :saleId')
            ->andWhere('buyerId = :buyerId')
            ->andWhere('salerId = :salerId')
            ->andWhere('orderSn LIKE :orderSnLike')
            ->andWhere('saleTookeen LIKE :saleTookeenLike')

            ->andWhere('commission  >=  :commission')
        
            ->andWhere('status = :status')
            ->andWhere('paidTime >= :startTimeGreaterThan')
            ->andWhere('paidTime < :startTimeLessThan');

        

        return $builder;
    }

    private function getTablename()
    {
        return self::TABLENAME;
    }

   

}