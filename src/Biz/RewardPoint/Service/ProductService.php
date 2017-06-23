<?php

namespace Biz\RewardPoint\Service;

interface ProductService
{
    public function createProduct($fields);

    public function updateProduct($id, array $fields);

    public function upShelves($id);

    public function downShelves($id);

    public function changeProductCover($id, $fields);

    public function deleteProduct($id);

    public function getProduct($id);

    public function findProductsByIds(array $ids);

    public function countProducts($conditions);

    public function searchProducts($conditions, $orderBy, $start, $limit);
}
