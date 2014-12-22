<?php
namespace Custom\Service\Carts;

interface CartsService
{
    public function getCart($id);

    public function searchCarts(array $conditions, array $sort, $start, $limit);

    public function searchCartsCount(array $conditions);

    public function addCart(array $carts);

    public function updateCart($id,$carts);

    public function deleteCart($id);
}