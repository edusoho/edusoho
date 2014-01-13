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

    public function getCommissionByProdAndUser($prodType,$prodId,$userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE prodType = ? and prodId=? and userId=? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($prodType,$prodId,$userId)) ? : null;
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

    public function addCommission($mysale)
    {
        $affected = $this->getConnection()->insert($this->table, $mysale);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert  mysale error.');
        }
        return $this->getCommission($this->getConnection()->lastInsertId());
    }

    public function updateCommission($id, $mysale)
    {
        $this->getConnection()->update($this->table, $mysale, array('id' => $id));
        return $this->getCommission($id);
    }

    public function deleteCommission($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }


    public function getCommissionBymTookeen($mTookeen)
    {
        $sql = "SELECT * FROM {$this->table} WHERE mTookeen = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($mTookeen)) ? : null;
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
            ->from(self::TABLENAME, 'mysale')
            ->andWhere('prodType = :prodType')
            ->andWhere('prodId = :prodId')
            ->andWhere('prodName LIKE :prodNameLike')
            ->andWhere('mTookeen LIKE :mTookeenLike')
            ->andWhere('userId = :userId')
            ->andWhere('managerId = :managerId')
            ->andWhere('validTime >= :startTimeGreaterThan')
            ->andWhere('validTime < :startTimeLessThan');

        

        return $builder;
    }

    private function getTablename()
    {
        return self::TABLENAME;
    }

   

}