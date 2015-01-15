<?php
namespace Custom\Service\Carts;

interface CartsService
{
    public function getCart($id);

    public function isAddedByUserId($userId, $itemId, $itemType);

    public function isAddedByUserKey($userKey, $itemId, $itemType);

    public function searchCarts(array $conditions, array $sort, $start, $limit);

    public function searchCartsCount(array $conditions);

    public function addCart(array $carts);

    public function updateCart($id,$carts);

    public function deleteCart($id);

    public function deleteCartsByIds($ids);

    public function findCartsByUserId($userId);

    public function findCartsByUserKey($userKey);
	
	public function findCurrentUserCarts();

    public function persistCarts($userId);    
}