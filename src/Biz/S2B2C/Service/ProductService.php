<?php

namespace Biz\S2B2C\Service;

interface ProductService
{
    public function searchProduct($conditions);

    public function searchSelectedItemProduct($conditions);

    public function createProduct($fields);

    public function getProduct($id);

    public function getProductBySupplierIdAndRemoteProductId($supplierId, $remoteProductId);

    public function findProductsBySupplierIdAndRemoteProductIds($supplierId, $remoteProductIds);

    public function findProductsBySupplierIdAndRemoteResourceTypeAndIds($supplierId, $productType, $remoteProductIds);

    public function getByTypeAndLocalResourceId($type, $localResourceId);
}
