<?php
namespace Topxia\Service\Sale\Dao\Impl;

use Topxia\Service\Common\BaseDao;

use Topxia\Service\Sale\Dao\OffsaleDao;

class OffsaleDaoImpl extends BaseDao implements OffsaleDao
{

    protected $table = 'offsale';

    public function getOffsale($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function findOffsalesByIds(array $ids)
    {
        if(empty($ids)){
            return array();
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function searchOffsales($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ? : array(); 
    }

    public function searchOffsaleCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function addOffsale($offsale)
    {
        $affected = $this->getConnection()->insert($this->table, $offsale);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert  offsale error.');
        }
        return $this->getOffsale($this->getConnection()->lastInsertId());
    }

    public function updateOffsale($id, $offsale)
    {
        $this->getConnection()->update($this->table, $offsale, array('id' => $id));
        return $this->getOffsale($id);
    }

    public function deleteOffsale($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }


    public function getOffsaleByCode($code)
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
            ->from(self::TABLENAME, 'offsale')
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