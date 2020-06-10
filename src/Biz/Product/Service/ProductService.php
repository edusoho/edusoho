<?php

namespace Biz\Product\Service;

interface ProductService
{
    public function getProduct($id);

    public function createProduct($product);

    public function updateProduct($id, $product);

    public function deleteProduct($id);

    public function searchProducts($conditions, $orderBys, $start, $limit, $columns = []);

    public function getProductByTargetIdAndType($targetId, $targetType);

    public function findProductsByIds($ids);
}
