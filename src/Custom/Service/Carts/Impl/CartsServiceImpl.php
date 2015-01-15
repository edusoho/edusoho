<?php
namespace Custom\Service\Carts\Impl;

use Topxia\Service\Common\BaseService;
use Custom\Service\Carts\CartsService;
use Topxia\Common\ArrayToolkit;
use Custom\Service\Carts\Type\CartItemTypeFactory;

class CartsServiceImpl extends BaseService  implements CartsService
{
    public function getCart ($id)
    {
        return $this->getCartsDao()->getCart($id);
    }

    public function isAddedByUserId($userId, $itemId, $itemType)
    {
        return $this->getCartsDao()->getCartByUserIdAndItemIdAndItemType($userId, $itemId, $itemType) ? true : false;
    }

    public function isAddedByUserKey($userKey, $itemId, $itemType)
    {
        return $this->getCartsDao()->getCartByuserKeyAndItemIdAndItemType($userKey, $itemId, $itemType) ? true : false;
    }

    public function getCartsCount()
    {
        $userId = $this->getCurrentUser()->id;
        $userKey = $_COOKIE['user-key'];
        if($userId) {
            $carts = $this->findCartsByUserId($userId);
        } else {
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

    public function addCart(array $fields)
    {
        $fields = $this->_filterCartFields($fields);
        $userId = $this->getCurrentUser()->id;
        $userKey = $_COOKIE['user-key'];
        $fields['userId'] = $userId;
        $fields['userKey'] = $userKey;
        if($userId) {
            if($this->isAddedByUserId($userId, $fields['itemId'], $fields['itemType'])) {
                return array();
            }
        } else {
            if($this->isAddedByUserId($userKey, $fields['itemId'], $fields['itemType'])) {
                return array();
            }
        }
        
        return $this->getCartsDao()->addCart($fields);
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

    public function findCurrentUserCarts()
    {
        $userId = $this->getCurrentUser()->id;
        $userKey = $_COOKIE['user-key'];
        if($userId) {
            $carts = $this->findCartsByUserId($userId);
        } else {
            $carts = $this->findCartsByUserKey($userKey);
        }

        $groupCarts = ArrayToolkit::group($carts, 'itemType');
        $itemResult = array();
        foreach ($groupCarts as $itemType => $carts) {
            $itemIds = ArrayToolkit::column($carts, 'itemId');
            $itemResult[$itemType] = CartItemTypeFactory::create($itemType)->getItemsAndExtra($itemIds, null);
        }
        return array(
            $groupCarts, $itemResult
        );
    }

    public function persistCarts($userId)
    {
        $userKey = $_COOKIE['user-key'];
        $carts = $this->findCartsByUserKey($userKey);
        if($carts) {
            $fields = array(
                'userKey' => null,
                'userId' => $userId
            );
            foreach ($carts as $cart) {
                $this->updateCart($cart['id'], $fields);
            }
        }
    }


    private function _filterCartFields($fields)
    {
        $fields = ArrayToolkit::filter($fields, array(
            'itemId' => 0,
            'itemType' => '',
            'userId' => 0,
            'userKey' => '',
            'createdTime' => 0
        ));
        
        return $fields;
    }

    private function getCartsDao()
    {
        return $this->createDao('Custom:Carts.CartsDao');
    }
}