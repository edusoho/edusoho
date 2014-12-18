<?php
namespace Custom\Service\Carts\Impl;

use Topxia\Service\Common\BaseService;
use Custom\Service\Carts\CartsService;

class CartsServiceImpl extends BaseService  implements CartsService
{
    public function getCarts ($id)
    {
        return $this->getCartsDao()->getCarts($id);
    }

    public function searchCarts (array $conditions, array $oderBy, $start, $limit)
    {
        return $this->getCartsDao()->searchCartss($conditions, $oderBy, $start, $limit);
    }

    public function searchCartsCount(array $conditions)
    {
        return $this->getCartsDao()->searchCartssCount($conditions);
    }

    public function findLimitCartsByUseId($limit,$userId)
    {
        return $this->getCartsDao()->findLimitCartsByUseId($limit,$userId);
    }

    public function addCarts(array $carts)
    {
        return $this->getCartsDao()->addCarts($carts);
    }

    public function updateCarts($id,$carts)
    {
        return $this->getCartsDao()->updateCarts($id,$carts);
    }


    public function deleteCarts ($id)
    {
        $this->getCartsDao()->deleteCarts($id);

    }

    private function getCartsDao()
    {
        return $this->createDao('Custom:Carts.CartsDao');
    }
}