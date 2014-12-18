<?php
namespace Topxia\Service\Carts;

interface CartsService
{

    public function getCarts($id);

    public function searchCarts($conditions, $sort, $start, $limit);

    public function searchCartsCount($conditions);

    public function addCarts($carts);

    public function updateCarts($id,$carts);

    public function deleteCarts($id);
}