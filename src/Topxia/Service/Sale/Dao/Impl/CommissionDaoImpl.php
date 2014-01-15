<?php
namespace Topxia\Service\Sale\Dao\Impl;

use Topxia\Service\Common\BaseDao;

use Topxia\Service\Sale\Dao\CommissionDao;

class CommissionDaoImpl extends BaseDao implements CommissionDao
{

    protected $table = 'mysale_commission';

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

    public function getCommissionByOrder($order)
    {
        $sql = "SELECT * FROM {$this->table} WHERE orderId = ? and orderSn = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($order['id'],$order['sn'])) ? : null;
    }


    private function _createSearchQueryBuilder($conditions)
    {

        if (isset($conditions['orderSn'])) {
            $conditions['orderSnLike'] = "%{$conditions['orderSn']}%";
            unset($conditions['orderSn']);
        }

        if (isset($conditions['mTookeen'])) {
            $conditions['mTookeenLike'] = "%{$conditions['mTookeen']}%";
            unset($conditions['mTookeen']);
        }

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from(self::TABLENAME, 'mysale_commission')
            ->andWhere('mysaleId = :mysaleId')
            ->andWhere('buyerId = :buyerId')
            ->andWhere('orderSnLike LIKE :orderSnLike')
            ->andWhere('mTookeen LIKE :mTookeenLike')
            ->andWhere('userId = :userId')
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