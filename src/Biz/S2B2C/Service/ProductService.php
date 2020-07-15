<?php

namespace Biz\S2B2C\Service;

interface ProductService
{
    const UPDATE_TYPE_MANUAL = 'manual';

    const UPDATE_TYPE_AUTO = 'auto';

    public function searchRemoteProducts($conditions);

    public function searchSelectedProducts($conditions);

    public function createProduct($fields);

    public function updateProduct($id, $productFields);

    public function deleteProduct($id);

    public function getProduct($id);

    public function getProductBySupplierIdAndRemoteProductId($supplierId, $remoteProductId);

    public function getProductBySupplierIdAndRemoteProductIdAndType($supplierId, $remoteProductId, $type);

    public function getProductBySupplierIdAndRemoteResourceIdAndType($supplierId, $remoteResourceId, $type);

    public function getByProductIdAndRemoteResourceIdAndType($productId, $remoteResourceId, $type);

    public function getProductBySupplierIdAndLocalResourceIdAndType($supplierId, $localResourceId, $type);

    public function searchProducts($conditions, $orderBys, $start, $limit, $columns = []);

    public function countProducts($conditions);

    public function findProductsBySupplierIdAndRemoteProductIds($supplierId, $remoteProductIds);

    public function findProductsBySupplierIdAndRemoteResourceTypeAndIds($supplierId, $productType, $remoteResourceIds);

    public function findProductsBySupplierIdAndRemoteResourceTypeAndProductIds($supplierId, $productType, $remoteProductIds);

    public function findProductsBySupplierIdAndProductTypeAndLocalResourceIds($supplierId, $productType, $localResourceIds);

    public function findProductsBySupplierIdAndProductType($supplierId, $productType);

    public function getByTypeAndLocalResourceId($type, $localResourceId);

    public function getProductUpdateType();

    public function setProductUpdateType($type);

    public function generateVersionChangeLogs($nowVersion, $productVersions);

    public function deleteByIds($ids);

    public function adoptProduct($s2b2cProductId);

    public function notifyNewVersionProduct($s2b2cProductId, $resourceCourseId, $versionData);

    public function findUpdatedVersionProductList();

    public function updateProductVersion($id);
}
