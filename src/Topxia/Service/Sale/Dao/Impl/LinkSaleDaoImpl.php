<?php
namespace Topxia\Service\Sale\Dao\Impl;

use Topxia\Service\Common\BaseDao;

use Topxia\Service\Sale\Dao\LinkSaleDao;

class LinkSaleDaoImpl extends BaseDao implements LinkSaleDao
{

    protected $table = 'sale_linksale';

    public function getLinkSale($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function findLinkSalesByIds(array $ids)
    {
        if(empty($ids)){
            return array();
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function getLinkSaleByProdAndUser($prodType,$prodId,$userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE prodType = ? and prodId=? and partnerId=? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($prodType,$prodId,$userId)) ? : null;
    }


    public function searchLinkSales($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ? : array(); 
    }

    public function searchLinkSaleCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function addLinkSale($linksale)
    {
        $affected = $this->getConnection()->insert($this->table, $linksale);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert  linksale error.');
        }
        return $this->getLinkSale($this->getConnection()->lastInsertId());
    }

    public function updateLinkSale($id, $linksale)
    {
        $this->getConnection()->update($this->table, $linksale, array('id' => $id));
        return $this->getLinkSale($id);
    }

    public function updateCourseLinkSale4unCustomized($adCommissionType,$adCommission,$adCommissionDay,$courseId)
    {
        
        $sql = "UPDATE   {$this->table}  SET adCommissionType=? ,adCommission=?, adCommissionDay=?  WHERE prodType='course' and  prodId = ?  and customized=0 ";

        return $this->getConnection()->executeQuery($sql, array($adCommissionType, $adCommission,$adCommissionDay,$courseId));
      
    }

    public function deleteLinkSale($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }


    public function getLinkSaleBymTookeen($mTookeen)
    {
        $sql = "SELECT * FROM {$this->table} WHERE mTookeen = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($mTookeen)) ? : null;
    }


    private function _createSearchQueryBuilder($conditions)
    {

        if (isset($conditions['prodName'])) {
            $conditions['prodNameLike'] = "%{$conditions['prodName']}%";
            unset($conditions['prodName']);
        }

        if (isset($conditions['mTookeen'])) {
            $conditions['mTookeenLike'] = "%{$conditions['mTookeen']}%";
            unset($conditions['mTookeen']);
        }

        if (isset($conditions['tUrl'])) {
            $conditions['tUrlLike'] = "%{$conditions['tUrl']}%";
            unset($conditions['tUrl']);
        }

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from(self::TABLENAME, 'sale_linksale')
            ->andWhere('prodType = :prodType')
            ->andWhere('prodId = :prodId')
            ->andWhere('prodName LIKE :prodNameLike')
            ->andWhere('mTookeen LIKE :mTookeenLike')
            ->andWhere('tUrl LIKE :tUrlLike')
            ->andWhere('partnerId = :partnerId')
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