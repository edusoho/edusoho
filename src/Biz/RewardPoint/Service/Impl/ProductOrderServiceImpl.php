<?php

namespace Biz\RewardPoint\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\RewardPoint\Service\ProductOrderService;
use Codeages\Biz\Framework\Event\Event;

class ProductOrderServiceImpl extends BaseService implements ProductOrderService
{
    public function createProductOrder($fields)
    {
        $fields['sn'] = $this->generateOrderSn();
        $this->validateProductOrderFields($fields);
        $fields = $this->filterFields($fields);
        $fields['status'] = 'created';

        return $this->getProductOrderDao()->create($fields);
    }

    public function updateProductOrder($id, $fields)
    {
        $fields = $this->filterFields($fields);

        return $this->getProductOrderDao()->update($id, $fields);
    }

    public function deliverProduct($id, $fields)
    {
        $fields['status'] = 'finished';
        $fields['sendTime'] = time();
        $fields = $this->filterFields($fields);

        return $this->getProductOrderDao()->update($id, $fields);
    }

    public function deleteProductOrder($id)
    {
        $productOrder = $this->getProductOrder($id);

        if (empty($productOrder)) {
            throw $this->createNotFoundException("order (#{$id}) not found");
        }

        return $this->getProductOrderDao()->delete($id);
    }

    public function getProductOrder($id)
    {
        return $this->getProductOrderDao()->get($id);
    }

    public function countProductOrders(array $conditions)
    {
        $conditions = $this->_prepareSearchConditions($conditions);

        return $this->getProductOrderDao()->count($conditions);
    }

    public function searchProductOrders(array $conditions, array $orderBys, $start, $limit)
    {
        $conditions = $this->_prepareSearchConditions($conditions);

        return $this->getProductOrderDao()->search($conditions, $orderBys, $start, $limit);
    }

    public function findProductOrdersByProductId($productId)
    {
        return $this->getProductOrderDao()->findByProductId($productId);
    }

    public function findProductOrdersByUserId($userId)
    {
        return $this->getProductOrderDao()->findByUserId($userId);
    }

    public function exchangeProduct($order)
    {
        $result = false;
        $product = $this->getProductService()->getProduct($order['productId']);
        $account = $this->getAccountService()->getAccountByUserId($order['userId']);

        if (empty($product)) {
            throw $this->createNotFoundException("product {$order['id']} not found");
        }

        if ($product['status'] == 'draft') {
            throw $this->createInvalidArgumentException("product {$order['id']} has been down");
        }

        if (empty($account)) {
            $account = $this->getAccountService()->createAccount(array('userId' => $order['userId']));
        }

        if ($account['balance'] >= $product['price']) {
            $order['title'] = '兑换商品"'.$product['title'].'"';
            $order['price'] = $product['price'];
            $order['status'] = 'created';
            $order = $this->createProductOrder($order);
            $flow = array(
                'userId' => $order['userId'],
                'type' => 'outflow',
                'amount' => $order['price'],
                'way' => 'exchange_product',
                'targetId' => $product['id'],
                'targetType' => 'product',
                'operator' => 0,
            );
            $this->getAccountFlowService()->createAccountFlow($flow);
            $this->getAccountService()->waveDownBalance($account['id'], $order['price']);
            $this->dispatchEvent('reward_point.product.exchange', new Event($order));
            $result = true;
        }

        return $result;
    }

    protected function validateProductOrderFields($fields)
    {
        if (!ArrayToolkit::requireds(
            $fields,
            array(
            'sn',
            'productId',
            'title',
            'price',
            'userId',
        ))) {
            throw $this->createInvalidArgumentException('parameters is invalid');
        }
    }

    protected function filterFields($fields)
    {
        return ArrayToolkit::parts(
            $fields,
            array(
                'sn',
                'productId',
                'title',
                'price',
                'userId',
                'consignee',
                'telephone',
                'email',
                'address',
                'sendTime',
                'message',
                'status',
            )
        );
    }

    protected function _prepareSearchConditions($conditions)
    {
        if (isset($conditions['keywordType'])) {
            $keywordType = $conditions['keywordType'];
            if ($keywordType == 'sn') {
                $conditions['sn'] = $conditions['keyword'];
            } elseif ($keywordType == 'nickName') {
                $user = $this->getUserService()->searchUsers(
                    array('nickname' => $conditions['keyword']),
                    array('createdTime' => 'DESC'),
                    0,
                    PHP_INT_MAX
                );
                $conditions['userIds'] = ArrayToolkit::column($user, 'id');
                $conditions['userIds'] = empty($conditions['userIds']) ? array(-1) : $conditions['userIds'];
            } elseif ($keywordType == 'title') {
                $conditions['titleLike'] = $conditions['keyword'];
            }
            $conditions['startDate'] = empty($conditions['startDate']) ? '' : strtotime($conditions['startDate']);
            $conditions['endDate'] = empty($conditions['endDate']) ? '' : strtotime($conditions['endDate']);
        }

        return $conditions;
    }

    protected function generateOrderSn()
    {
        return 'RPO'.date('YmdHis', time()).mt_rand(10000, 99999);
    }

    protected function getProductService()
    {
        return $this->createService('RewardPoint:ProductService');
    }

    protected function getAccountService()
    {
        return $this->createService('RewardPoint:AccountService');
    }

    protected function getAccountFlowService()
    {
        return $this->createService('RewardPoint:AccountFlowService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getProductOrderDao()
    {
        return $this->createDao('RewardPoint:ProductOrderDao');
    }
}
