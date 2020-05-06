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
           'serializes' => [],
           'conditions' => [
                'id = :id',
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
        return $this->getByFields(array('productType' => $type, 'localResourceId' => $localResourceId));
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
}
