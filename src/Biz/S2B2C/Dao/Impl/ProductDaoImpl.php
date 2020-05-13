<?php

namespace Biz\S2B2C\Dao\Impl;

use Biz\S2B2C\Dao\ProductDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ProductDaoImpl extends GeneralDaoImpl implements ProductDao
{
    protected $table = 's2b2c_product';

    public function declares()
    {
        return [
           'timestamps' => ['createdTime', 'updatedTime'],
           'serializes' => ['changelog' => 'json'],
           'conditions' => [
               'id = :id',
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

    public function getByTypeAndLocalResourceId($type, $localResourceId)
    {
        return $this->getByFields(['productType' => $type, 'localResourceId' => $localResourceId]);
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

    public function findBySupplierIdAndRemoteResourceTypeAndIds($supplierId, $productType, $remoteResourceIds)
    {
        $marks = str_repeat('?,', count($remoteResourceIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE supplierId= ? AND productType = ? AND remoteResourceId IN ({$marks});";

        return $this->db()->fetchAll($sql, array_merge([$supplierId, $productType], array_values($remoteResourceIds)));
    }

    public function findBySupplierIdAndProductTypeAndLocalResourceIds($supplierId, $productType, $localResourceIds)
    {
        $marks = str_repeat('?,', count($localResourceIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE supplierId= ? AND productType = ? AND localResourceId IN ({$marks});";

        return $this->db()->fetchAll($sql, array_merge([$supplierId, $productType], array_values($localResourceIds)));
    }
}
