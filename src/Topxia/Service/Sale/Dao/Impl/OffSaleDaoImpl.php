<?php
namespace Topxia\Service\Sale\Dao\Impl;

use Topxia\Service\Common\BaseDao;

use Topxia\Service\Sale\Dao\OffSaleDao;

class OffSaleDaoImpl extends BaseDao implements OffSaleDao
{

    protected $table = 'sale_offsale';

    public function getOffSale($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function findOffSalesByIds(array $ids)
    {
        if(empty($ids)){
            return array();
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function searchOffSales($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ? : array(); 
    }

    public function searchOffSaleCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function addOffSale($offsale)
    {
        $affected = $this->getConnection()->insert($this->table, $offsale);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert  offsale error.');
        }
        return $this->getOffSale($this->getConnection()->lastInsertId());
    }

    public function updateOffSale($id, $offsale)
    {
        $this->getConnection()->update($this->table, $offsale, array('id' => $id));
        return $this->getOffSale($id);
    }

    public function deleteOffSale($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }


    public function getOffSaleByCode($code)
    {
        $sql = "SELECT * FROM {$this->table} WHERE promocode = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($code)) ? : null;
    }


    private function _createSearchQueryBuilder($conditions)
    {

        if (isset($conditions['prodName'])) {
            $conditions['prodNameLike'] = "%{$conditions['prodName']}%";
            unset($conditions['prodName']);
        }

        if (isset($conditions['promoName'])) {
            $conditions['promoNameLike'] = "%{$conditions['promoName']}%";
            unset($conditions['promoName']);
        }

        if (isset($conditions['promoCode'])) {
            $conditions['promoCodeLike'] = "%{$conditions['promoCode']}%";
            unset($conditions['promoCode']);
        }

       

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from(self::TABLENAME, 'sale_offsale')
            ->andWhere('prodType = :prodType')
            ->andWhere('prodId = :prodId')
            ->andWhere('prodName LIKE :prodNameLike')
            ->andWhere('promoName LIKE :promoNameLike')
            ->andWhere('promoCode LIKE :promoCodeLike')
            ->andWhere('reuse = :reuse')
            ->andWhere('valid = :valid')
            ->andWhere('validTime >= :startTimeGreaterThan')
            ->andWhere('validTime < :startTimeLessThan');

        

        return $builder;
    }

    private function getTablename()
    {
        return self::TABLENAME;
    }

   

}