<?php

namespace Topxia\Service\Carts\Dao;

interface CartsDao
{
    public function getCarts($id);

    public function searchCartss($conditions, $oderBy, $start, $limit);

    public function searchCartsCount(array $conditions);

    public function addCarts(array $carts);

    public function updateCarts($id, array $carts);

    public function deleteCarts($id);
}