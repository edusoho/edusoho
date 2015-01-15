<?php

namespace Custom\Service\Carts\Dao;

interface CartsDao
{
    public function getCart($id);

    public function getCartByUserIdAndItemIdAndItemType($userId, $itemId, $itemType);

    public function getCartByuserKeyAndItemIdAndItemType($userKey, $itemId, $itemType);

    public function searchCarts($conditions, $oderBy, $start, $limit);

    public function searchCartsCount(array $conditions);

    public function addCart(array $carts);

    public function updateCart($id, array $carts);

    public function deleteCart($id);

    public function findCartsByUserId($userId);

    public function findCartsByUserKey($userKey);
}