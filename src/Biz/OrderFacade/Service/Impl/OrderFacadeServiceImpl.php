<?php

namespace Biz\OrderFacade\Service\Impl;

use Biz\BaseService;
use Biz\OrderFacade\Currency;
use Biz\OrderFacade\Product\Product;
use Biz\OrderFacade\Service\OrderFacadeService;
use Codeages\Biz\Framework\Order\Service\OrderService;
use AppBundle\Common\MathToolkit;
use Codeages\Biz\Framework\Service\Exception\ServiceException;

class OrderFacadeServiceImpl extends BaseService implements OrderFacadeService
{
    public function create(Product $product)
    {
        $product->validate();

        $user = $this->biz['user'];
        /* @var $currency Currency */
        $currency = $this->biz['currency'];
        $orderFields = array(
            'title' => $product->title,
            'user_id' => $user['id'],
            'created_reason' => 1,
            'price_type' => $currency->isoCode,
            'currency_exchange_rate' => $currency->exchangeRate,
        );

        $orderItems = $this->makeOrderItems($product);

        $order = $this->getOrderService()->createOrder($orderFields, $orderItems);

        return $order;
    }

    private function makeOrderItems(Product $product)
    {
        $orderItem = array(
            'target_id' => $product->targetId,
            'target_type' => $product->targetType,
            'price_amount' => $product->price,
            'pay_amount' => $product->getPayablePrice(),
            'title' => $product->title,
        );

        $orderItem = MathToolkit::multiply(
            $orderItem,
            array('price_amount', 'pay_amount'),
            100
        );
        $deducts = array();

        foreach ($product->pickedDeducts as $deduct) {
            $deduct = MathToolkit::multiply($deduct, array('deduct_amount'), 100);
            $deducts[] = array(
                'deduct_id' => $deduct['deduct_id'],
                'deduct_type' => $deduct['deduct_type'],
                'deduct_amount' => $deduct['deduct_amount'],
            );
        }

        if ($deducts) {
            $orderItem['deducts'] = $deducts;
        }

        return array($orderItem);
    }

    public function checkOrderBeforePay($sn)
    {
        $order = $this->getOrderService()->getOrderBySn($sn);

        if (!$order) {
            throw new ServiceException('订单不存在', 2008);
        }

        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw new ServiceException('用户未登录，不能支付。');
        }

        if ($order['user_id'] != $user['id']) {
            throw new ServiceException('不是您的订单，不能支付', 2004);
        }

        if ($order['status'] != 'created') {
            throw new ServiceException('订单状态被更改，不能支付', 2005);
        }

        $this->biz['order.pay.checker']->check($order);

        return $order;
    }

    public function createFreeOrder(Product $product)
    {
        $order = $this->createOrder($product);

        $this->getOrderService()->setOrderPaying($order['id'], array());

        $data = array(
            'trade_sn' => $trade['trade_sn'],
            'pay_time' => $args['paid_time'],
            'order_sn' => $trade['order_sn']
        );
        $this->getOrderService()->setOrderPaid($data);

    }

    /**
     * @return OrderService
     */
    private function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

    protected function getTargetProduct($productType)
    {
        $biz = $this->getBiz();

        if (empty($biz["order.product.{$productType}"])) {
            return null;
        }
        return $biz["order.product.{$productType}"];
    }
}
