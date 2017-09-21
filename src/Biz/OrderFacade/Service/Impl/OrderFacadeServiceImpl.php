<?php

namespace Biz\OrderFacade\Service\Impl;

use Biz\BaseService;
use Biz\OrderFacade\Command\OrderPayCheck\OrderPayChecker;
use Biz\OrderFacade\Currency;
use Biz\OrderFacade\Exception\OrderPayCheckException;
use Biz\OrderFacade\Product\Product;
use Biz\OrderFacade\Service\OrderFacadeService;
use AppBundle\Common\MathToolkit;
use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Order\Service\OrderService;
use Codeages\Biz\Framework\Order\Service\WorkflowService;

class OrderFacadeServiceImpl extends BaseService implements OrderFacadeService
{
    public function create(Product $product)
    {
        $product->validate();

        $user = $this->biz['user'];
        /* @var $currency Currency */
        $currency = $this->getCurrency();
        $orderFields = array(
            'title' => $product->title,
            'user_id' => $user['id'],
            'created_reason' => 'site.join_by_purchase',
            'price_type' => $currency->isoCode,
            'currency_exchange_rate' => $currency->exchangeRate,
            'refund_deadline' => $this->getRefundDeadline(),
        );

        $orderItems = $this->makeOrderItems($product);

        $order = $this->getWorkflowService()->start($orderFields, $orderItems);

        return $order;
    }

    private function getRefundDeadline()
    {
        $refundSetting = $this->getSettingService()->get('refund');
        $timeInterval = empty($refundSetting['maxRefundDays']) ? 0 : $refundSetting['maxRefundDays'] * 24 * 60 * 60;
        return time() + $timeInterval;
    }

    private function makeOrderItems(Product $product)
    {
        $orderItem = array(
            'target_id' => $product->targetId,
            'target_type' => $product->targetType,
            'price_amount' => $product->originPrice,
            'pay_amount' => $product->getPayablePrice(),
            'title' => $product->title,
            'num' => $product->num,
            'unit' => $product->unit,
            'create_extra' => $product->getCreateExtra(),
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

    public function getTradePayCashAmount($order, $coinAmount)
    {
        $orderCoinAmount = $this->getCurrency()->convertToCoin($order['pay_amount'] / 100);

        return $this->getCurrency()->convertToCNY($orderCoinAmount - $coinAmount);
    }

    public function createSpecialOrder(Product $product, $userId, $params = array())
    {
        $currency = $this->getCurrency();
        $orderFields = array(
            'title' => $product->title,
            'user_id' => $userId,
            'created_reason' => empty($params['created_reason']) ? '' : $params['created_reason'],
            'source' => empty($params['source']) ? 'self' : $params['source'],
            'price_type' => empty($params['price_type']) ? $currency->isoCode : $params['price_type'],
        );

        $orderItems = $this->makeOrderItems($product);

        $order = $this->getWorkflowService()->start($orderFields, $orderItems);

        $this->getWorkflowService()->paying($order['id'], array());

        $data = array(
            'trade_sn' => '',
            'pay_time' => 0,
            'order_sn' => $order['sn'],
        );
        $order = $this->getWorkflowService()->paid($data);

        return $order;
    }

    public function getOrderProduct($targetType, $params)
    {
        if (!empty($this->biz['order.product.'.$targetType])) {
            /* @var $product Product */
            $product = $this->biz['order.product.'.$targetType];
            $product->init($params);

            return $product;
        } else {
            throw $this->createServiceException("The {$targetType} product not found");
        }
    }

    public function getOrderProductByOrderItem($orderItem)
    {
        if (!empty($this->biz['order.product.'.$orderItem['target_type']])) {
            /* @var $product Product */
            $product = $this->biz['order.product.'.$orderItem['target_type']];
            $product->init(array(
                'targetId' => $orderItem['target_id'],
                'num' => $orderItem['num'],
                'unit' => $orderItem['unit'],
            ));

            return $product;
        } else {
            throw $this->createServiceException("The {$orderItem['target_type']} product not found");
        }
    }

    public function payingOrder($orderSn, $params)
    {
        $order = $this->checkOrderBeforePay($orderSn, $params);

        $trade = $this->makeTrade($order, $params);

        $this->getWorkflowService()->paying($order['id'], array());

        return $trade;
    }

    private function checkOrderBeforePay($sn, $params)
    {
        $order = $this->getOrderService()->getOrderBySn($sn);

        if (!$order) {
            throw new OrderPayCheckException('order.pay_check_msg.order_not_exist', 2004);
        }

        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw new OrderPayCheckException('order.pay_check_msg.user_not_login', 20005);
        }

        if ($order['user_id'] != $user['id']) {
            throw new OrderPayCheckException('order.pay_check_msg.not_same_user', 2006);
        }

        /** @var $orderPayChecker OrderPayChecker */
        $orderPayChecker = $this->biz['order.pay.checker'];
        $orderPayChecker->check($order, $params);

        return $order;
    }

    private function makeTrade($order, $params)
    {
        $coinAmount = isset($params['coinAmount']) ? $params['coinAmount'] : 0;
        $trade = array(
            'goods_title' => $order['title'],
            'goods_detail' => '',
            'order_sn' => $order['sn'],
            'amount' => $order['pay_amount'],
            'platform' => $params['payment'],
            'user_id' => $order['user_id'],
            'coin_amount' => $coinAmount * 100,
            'cash_amount' => $this->getTradePayCashAmount($order, $coinAmount) * 100,
            'create_ip' => isset($params['clientIp']) ? $params['clientIp'] : '127.0.0.1',
            'price_type' => 'money',
            'type' => 'purchase',
            'attach' => array(
                'user_id' => $order['user_id'],
            ),
        );

        return $trade;
    }

    /**
     * @return Currency
     */
    private function getCurrency()
    {
        return $this->biz['currency'];
    }

    /**
     * @return WorkflowService
     */
    private function getWorkflowService()
    {
        return $this->createService('Order:WorkflowService');
    }

    /**
     * @return OrderService
     */
    private function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
