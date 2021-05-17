<?php

namespace Biz\MultiClass\Service;

interface MultiClassProductService
{
    public function getProductByTitle($title);

    public function createProduct($product);

    public function getProductById($id);

    public function updateProductById($id, $fields);

    public function deleteProductById($id);
}
