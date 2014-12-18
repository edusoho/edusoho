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
        return $this->getCartsDao()->searchCarts($conditions, $oderBy, $start, $limit);
    }

    public function searchCartsCount(array $conditions)
    {
        return $this->getCartsDao()->searchCartsCount($conditions);
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
        return $this->getCartsDao()->deleteCarts($id);

    }

    public function deleteCartsByIds($ids)
    {
        if (count($ids) == 1) {
            $this->deleteCarts($ids[0]);
        } else {
            foreach ($ids as $key => $id) {
                $this->deleteCarts($id);
            }
        }

        return true;
    }

    private function getCartsDao()
    {
        return $this->createDao('Custom:Carts.CartsDao');
    }
}