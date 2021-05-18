<?php

namespace Biz\MultiClass\Service;

interface MultiClassProductService
{
    public function getProductByTitle($title);

    public function createProduct($product);

    public function getProduct($id);

    public function updateProductById($id, $fields);
}
