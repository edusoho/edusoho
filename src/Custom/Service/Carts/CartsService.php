<?php
namespace Custom\Service\Carts;

interface CartsService
{
    public function getCarts($id);

    public function searchCarts(array $conditions, array $sort, $start, $limit);

    public function searchCartsCount(array $conditions);

    public function addCarts(array $carts);

    public function updateCarts($id,$carts);

    public function deleteCarts($id);
}