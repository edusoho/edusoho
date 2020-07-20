<?php

namespace Biz\S2B2C\Dao\Impl;

use Biz\S2B2C\Dao\ProductDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ProductDaoImpl extends AdvancedDaoImpl implements ProductDao
{
    protected $table = 's2b2c_product';

    public function declares()
    {
        return [
           'timestamps' => ['createdTime', 'updatedTime'],
           'serializes' => ['changelog' => 'json'],
           'conditions' => [
               'id = :id',
               'supplierId = :supplierId',
               'productType = :productType',
               'remoteResourceId = :remoteResourceId',
               'localResourceId = :localResourceId',
               'remoteProductId = :remoteProductId',
           ],
           'orderbys' => ['id'],
       ];
    }

    public function getBySupplierIdAndRemoteProductId($supplierId, $remoteProductId)
    {
        return $this->getByFields(['supplierId' => $supplierId, 'remoteProductId' => $remoteProductId]);
    }

    public function getBySupplierIdAndRemoteProductIdAndType($supplierId, $remoteProductId, $type)
    {
        return $this->getByFields(['supplierId' => $supplierId, 'remoteProductId' => $remoteProductId, 'productType' => $type]);
    }

    public function getBySupplierIdAndRemoteResourceIdAndType($supplierId, $remoteResourceId, $type)
    {
        return $this->getByFields(['supplierId' => $supplierId, 'remoteResourceId' => $remoteResourceId, 'productType' => $type]);
    }

    public function getBySupplierIdAndLocalResourceIdAndType($supplierId, $localResourceId, $type)
    {
        return $this->getByFields(['supplierId' => $supplierId, 'localResourceId' => $localResourceId, 'productType' => $type]);
    }

    public function getByRemoteProductIdRemoteResourceIdAndType($productId, $remoteResourceId, $type)
    {
        return $this->getByFields(['remoteProductId' => $productId, 'remoteResourceId' => $remoteResourceId, 'productType' => $type]);
    }

    public function getByTypeAndLocalResourceId($type, $localResourceId)
    {
        return $this->getByFields(['productType' => $type, 'localResourceId' => $localResourceId]);
    }

    /**
     * @param $supplierId
     * @param $productType
     *
     * @return array
     */
    public function findBySupplierIdAndProductType($supplierId, $productType)
    {
        return $this->findByFields(['productType' => $productType, 'supplierId' => $supplierId]);
    }

    /**
     * @param $supplierId
     * @param $remoteProductIds
     *
     * @return mixed[]
     */
    public function findBySupplierIdAndRemoteProductIds($supplierId, $remoteProductIds)
    {
        $marks = str_repeat('?,', count($remoteProductIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE supplierId= ? AND remoteProductId IN ({$marks});";

        return $this->db()->fetchAll($sql, array_merge([$supplierId], array_values($remoteProductIds)));
    }

    public function findBySupplierIdAndRemoteProductId($supplierId, $remoteProductId)
    {
        return $this->findByFields(['supplierId' => $supplierId, 'remoteProductId' => $remoteProductId]);
    }

    public function findBySupplierIdAndRemoteResourceTypeAndIds($supplierId, $productType, $remoteResourceIds)
    {
        $marks = str_repeat('?,', count($remoteResourceIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE supplierId= ? AND productType = ? AND remoteResourceId IN ({$marks});";

        return $this->db()->fetchAll($sql, array_merge([$supplierId, $productType], array_values($remoteResourceIds)));
    }

    public function findBySupplierIdAndRemoteResourceTypeAndProductIds($supplierId, $productType, $remoteProductIds)
    {
        $marks = str_repeat('?,', count($remoteProductIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE supplierId= ? AND productType = ? AND remoteProductId IN ({$marks});";

        return $this->db()->fetchAll($sql, array_merge([$supplierId, $productType], array_values($remoteProductIds)));
    }

    public function findBySupplierIdAndProductTypeAndLocalResourceIds($supplierId, $productType, $localResourceIds)
    {
        $marks = str_repeat('?,', count($localResourceIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE supplierId= ? AND productType = ? AND localResourceId IN ({$marks});";

        return $this->db()->fetchAll($sql, array_merge([$supplierId, $productType], array_values($localResourceIds)));
    }

    public function deleteByIds($ids)
    {
        $marks = str_repeat('?,', count($ids) - 1).'?';
        $sql = "DELETE FROM {$this->table} WHERE id IN ({$marks});";

        return $this->db()->executeUpdate($sql, $ids);
    }

    public function findRemoteVersionGTLocalVersion()
    {
        $sql = "SELECT * FROM {$this->table} WHERE remoteVersion > localVersion AND productType = ? ORDER BY ? ?;";

        return $this->db()->fetchAll($sql, ['course_set', 'createdTime', 'desc']);
    }
}
