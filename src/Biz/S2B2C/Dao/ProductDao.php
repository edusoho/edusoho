<?php

namespace Biz\S2B2C\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface ProductDao extends AdvancedDaoInterface
{
    public function getBySupplierIdAndRemoteProductId($supplierId, $remoteProductId);

    public function findBySupplierIdAndRemoteProductIds($supplierId, $remoteProductIds);

    public function findBySupplierIdAndRemoteResourceTypeAndIds($supplierId, $productType, $remoteResourceIds);

    public function getByTypeAndLocalResourceId($type, $localResourceId);

    public function findBySupplierIdAndProductTypeAndLocalResourceIds($supplierId, $productType, $localResourceIds);

    public function findBySupplierIdAndProductType($supplierId, $productType);

    public function getBySupplierIdAndRemoteResourceIdAndType($supplierId, $remoteResourceId, $type);

    public function getBySupplierIdAndLocalResourceIdAndType($supplierId, $localResourceId, $type);

    public function deleteByIds($ids);
}
