<?php
namespace Custom\Service\Carts\Impl;

use Topxia\Service\Common\BaseService;
use Custom\Service\Carts\CartsService;

class CartsServiceImpl extends BaseService  implements CartsService
{
    public function getCart ($id)
    {
        return $this->getCartsDao()->getCart($id);
    }

    public function getCartsCount()
    {
        if (empty($_COOKIE['user-key'])){
            return '0';
        } else {
            $userKey = $_COOKIE['user-key'];
            $carts = $this->findCartsByUserKey($userKey);
        }

        return count($carts);
    }

    public function searchCarts (array $conditions, array $oderBy, $start, $limit)
    {
        return $this->getCartsDao()->searchCarts($conditions, $oderBy, $start, $limit);
    }

    public function searchCartsCount(array $conditions)
    {
        return $this->getCartsDao()->searchCartsCount($conditions);
    }

    public function findCartsByUserId($userId)
    {
        return $this->getCartsDao()->findCartsByUserId($userId);
    }

    public function findCartsByUserKey($userKey)
    {
        return $this->getCartsDao()->findCartsByUserKey($userKey);
    }

    public function addCart(array $carts)
    {
        return $this->getCartsDao()->addCart($carts);
    }

    public function updateCart($id,$carts)
    {
        return $this->getCartsDao()->updateCart($id,$carts);
    }


    public function deleteCart ($id)
    {
        $this->getCartsDao()->deleteCart($id);

    }

    public function deleteCartsByIds($ids)
    {
        if (count($ids) == 1) {
            $this->deleteCart($ids[0]);
        } else {
            foreach ($ids as $key => $id) {
                $this->deleteCart($id);
            }
        }

        return true;
    }

    private function getCartsDao()
    {
        return $this->createDao('Custom:Carts.CartsDao');
    }
}