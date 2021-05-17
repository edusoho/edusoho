<?php

namespace Biz\MultiClass\Service;

interface MultiClassProductService
{
    public function getProductByTitle($title);

    public function createProduct($product);

    public function searchProducts(array $conditions, array $oderBy, $start, $limit);

    public function countProducts(array $conditions);
}
