<?php

namespace Biz\S2B2C\Service;

interface ProductService
{
    public function searchRemoteProducts($conditions);

    public function searchSelectedItemProduct($conditions);

    public function createProduct($fields);

    public function updateProduct($id, $productFields);

    public function deleteProduct($id);

    public function getProduct($id);

    public function getProductBySupplierIdAndRemoteProductId($supplierId, $remoteProductId);

    public function searchProducts($conditions, $orderBys, $start, $limit, $columns = []);

    public function countProducts($conditions);

    public function findProductsBySupplierIdAndRemoteProductIds($supplierId, $remoteProductIds);

    public function findProductsBySupplierIdAndRemoteResourceTypeAndIds($supplierId, $productType, $remoteProductIds);

    public function findProductsBySupplierIdAndProductTypeAndLocalResourceIds($supplierId, $productType, $localResourceIds);

    public function getByTypeAndLocalResourceId($type, $localResourceId);
}
