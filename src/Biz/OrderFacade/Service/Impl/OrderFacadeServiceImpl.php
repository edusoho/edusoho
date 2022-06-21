<?php

namespace Biz\OrderFacade\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\MathToolkit;
use Biz\BaseService;
use Biz\Order\OrderException;
use Biz\OrderFacade\Currency;
use Biz\OrderFacade\Exception\OrderPayCheckException;
use Biz\OrderFacade\Product\Product;
use Biz\OrderFacade\Service\OrderFacadeService;
use Biz\OrderFacade\Service\ProductDealerService;
use Biz\System\Service\LogService;
use Biz\System\Service\SettingService;
use Biz\User\UserException;
use Codeages\Biz\Order\Service\OrderService;
use Codeages\Biz\Order\Service\WorkflowService;
use Codeages\Biz\Order\Status\Order\FailOrderStatus;
use Codeages\Biz\Order\Status\Order\FinishedOrderStatus;
use Codeages\Biz\Order\Status\Order\PaidOrderStatus;
use Codeages\Biz\Order\Status\Order\SuccessOrderStatus;

class OrderFacadeServiceImpl extends BaseService implements OrderFacadeService
{
    private $deductTypeName = ['discount' => '打折', 'coupon' => '优惠券', 'paidCourse' => '班级课程抵扣', 'adjust_price' => '改价'];

    /*
     * 用于处理 商品
     * ProductDealer 数组
     */
    private $dealers;

    public function create(Product $product)
    {
        $product->validate();
        $product = $this->dealBeforeCreate($product);

        $user = $this->biz['user'];
        /* @var $currency Currency */
        $currency = $this->getCurrency();
        $orderFields = [
            'title' => $product->title,
            'user_id' => $user['id'],
            'created_reason' => 'site.join_by_purchase',
            'price_type' => 'CNY',
            'currency_exchange_rate' => $currency->exchangeRate,
            'expired_refund_days' => $this->getRefundDays(),
        ];

        $orderItems = $this->makeOrderItems($product);

        $order = $this->getWorkflowService()->start($orderFields, $orderItems);

        return $order;
    }

    public function getRefundDays()
    {
        $refundSetting = $this->getSettingService()->get('refund');

        return empty($refundSetting['maxRefundDays']) ? 0 : $refundSetting['maxRefundDays'];
    }

    public function isOrderPaid($orderId)
    {
        if ($order = $this->getOrderService()->getOrder($orderId)) {
            return in_array($order['status'], [
                SuccessOrderStatus::NAME,
                PaidOrderStatus::NAME,
                FailOrderStatus::NAME,
                FinishedOrderStatus::NAME,
            ]);
        } else {
            return false;
        }
    }

    private function makeOrderItems(Product $product)
    {
        $orderItem = [
            'target_id' => in_array($product->targetType, ['course', 'classroom']) ? $product->goodsSpecsId : $product->targetId,
            'target_type' => $product->targetType,
            'price_amount' => $product->originPrice,
            'pay_amount' => $product->getPayablePrice(),
            'title' => $product->title,
            'num' => $product->num,
            'unit' => $product->unit,
            'create_extra' => $product->getCreateExtra(),
            'snapshot' => $product->getSnapShot(),
        ];

        $orderItem = MathToolkit::multiply(
            $orderItem,
            ['price_amount', 'pay_amount'],
            100
        );
        $deducts = [];

        foreach ($product->pickedDeducts as $deduct) {
            $deduct = MathToolkit::multiply($deduct, ['deduct_amount'], 100);
            if (in_array($deduct['deduct_type'], array_keys($this->deductTypeName))) {
                $typeName = $this->deductTypeName[$deduct['deduct_type']];
            } else {
                $typeName = empty($deduct['deduct_type_name']) ? '' : $deduct['deduct_type_name'];
            }
            $deducts[] = [
                'deduct_id' => $deduct['deduct_id'],
                'deduct_type' => $deduct['deduct_type'],
                'deduct_type_name' => $typeName,
                'deduct_amount' => $deduct['deduct_amount'],
                'snapshot' => empty($deduct['snapshot']) ? null : $deduct['snapshot'],
            ];
        }

        if ($deducts) {
            $orderItem['deducts'] = $deducts;
        }

        return [$orderItem];
    }

    public function getTradePayCashAmount($order, $coinAmount)
    {
        $orderCoinAmount = $this->getCurrency()->convertToCoin($order['pay_amount'] / 100);

        return $this->getCurrency()->convertToCNY($orderCoinAmount - $coinAmount);
    }

    /**
     * @param $type 用于查找相应的实现类， 目前分为 'OrderFacade' 和 'Marketing'
     */
    public function createSpecialOrder(Product $product, $userId, $params = [], $type = 'OrderFacade')
    {
        $sepcialOrderService = $this->createService($type.':SpecialOrderService');
        $orderFields = [
            'title' => $product->title,
            'user_id' => $userId,
            'source' => empty($params['source']) ? 'self' : $params['source'],
            'price_type' => 'CNY',
            'created_reason' => empty($params['created_reason']) ? '' : $params['created_reason'],
            'create_extra' => empty($params['create_extra']) ? '' : $params['create_extra'],
            'deducts' => empty($params['deducts']) ? [] : $params['deducts'],
        ];
        $joinType = $orderFields['create_extra']['joinType'] ?? '';
        if ('SCRM' === $joinType) {
            $orderFields['source'] = 'scrm';
        }
        $orderFields = $sepcialOrderService->beforeCreateOrder($orderFields, $params);
        $orderItems = $this->makeOrderItems($product);
        $order = $this->getWorkflowService()->start($orderFields, $orderItems);
        $price = empty($orderFields['create_extra']['price']) ? 0 : $orderFields['create_extra']['price'];
        //修复来自SCRM订单价格不能大于原价
        if ($price > 0 && !MathToolkit::isEqual($order['pay_amount'], MathToolkit::simple($price, 100)) && 'SCRM' != $joinType) {
            $this->getWorkflowService()->adjustPrice($order['id'], MathToolkit::simple($price, 100));
        }
        if ('SCRM' == $joinType && $order['pay_amount'] > MathToolkit::simple($price, 100)) {
            $this->getWorkflowService()->adjustPrice($order['id'], MathToolkit::simple($price, 100));
        }
        if ('SCRM' == $joinType && 0 == (int) $order['pay_amount']) {
            return $order;
        }
        $this->getWorkflowService()->paying($order['id'], []);
        $data = [
            'trade_sn' => '',
            'pay_time' => 0,
            'order_sn' => $order['sn'],
        ];
        $data = $sepcialOrderService->beforePayOrder($data, $params);
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
            throw OrderPayCheckException::NOTFOUND_PRODUCT();
        }
    }

    public function getOrderProductByOrderItem($orderItem)
    {
        if (!empty($this->biz['order.product.'.$orderItem['target_type']])) {
            /* @var $product Product */
            $product = $this->biz['order.product.'.$orderItem['target_type']];
            $product->init([
                'targetId' => $orderItem['target_id'],
                'num' => $orderItem['num'],
                'unit' => $orderItem['unit'],
                'orderItemId' => $orderItem['id'],
            ]);

            return $product;
        } else {
            throw OrderPayCheckException::NOTFOUND_PRODUCT();
        }
    }

    public function sumOrderItemPayAmount($conditions)
    {
        return $this->getOrderService()->sumOrderItemPayAmount($conditions);
    }

    public function sumOrderPayAmount($conditions)
    {
        $orderItems = $this->getOrderService()->searchOrderItems($conditions, [], 0, PHP_INT_MAX, ['order_id']);
        $paidAmount = empty($orderItems) ? 0 : $this->getOrderService()->sumPaidAmount(['ids' => array_column($orderItems, 'order_id')]);

        return empty($paidAmount['payAmount']) ? 0 : $paidAmount['payAmount'];
    }

    public function checkOrderBeforePay($sn, $params)
    {
        $order = $this->getOrderService()->getOrderBySn($sn);

        if (!$order) {
            $this->createNewException(OrderException::NOTFOUND_ORDER());
        }

        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        if ($order['user_id'] != $user['id']) {
            $this->createNewException(OrderException::BEYOND_AUTHORITY());
        }

        /** @var $orderPayChecker OrderPayChecker */
        $orderPayChecker = $this->biz['order.pay.checker'];
        $orderPayChecker->check($order, $params);

        return $order;
    }

    public function adjustOrderPrice($orderId, $newPayAmount)
    {
        $order = $this->getOrderService()->getOrder($orderId);

        if (!MathToolkit::isEqual($order['pay_amount'], $newPayAmount)) {
            $adjustDeduct = $this->getWorkflowService()->adjustPrice($orderId, $newPayAmount);

            return $adjustDeduct;
        }

        return null;
    }

    public function getOrderAdjustInfo($order)
    {
        $deducts = $this->getOrderService()->findOrderItemDeductsByOrderId($order['id']);
        list($totalDeductAmountExcludeAdjust, $adjustDeduct) = $this->getTotalDeductExcludeAdjust($deducts);
        $adjustDeduct['payAmountExcludeAdjust'] = MathToolkit::simple($order['price_amount'] - $totalDeductAmountExcludeAdjust, 0.01);
        $adjustDeduct['adjustPrice'] = empty($adjustDeduct['deduct_amount']) ? '' : MathToolkit::simple($adjustDeduct['deduct_amount'], 0.01);
        $adjustDeduct['adjustDiscount'] = empty($adjustDeduct['deduct_amount']) ? '' : round(MathToolkit::simple($order['pay_amount'], 0.01) * 10 / $adjustDeduct['payAmountExcludeAdjust'], 2);

        return $adjustDeduct;
    }

    public function addDealer($dealer)
    {
        if (empty($this->dealers)) {
            $this->dealers = [];
        }

        $class = $dealer->getClass();
        if (!($class instanceof ProductDealerService)) {
            $this->createNewException(OrderPayCheckException::INSTANCE_ERROR());
        }

        $this->dealers[] = $dealer;
    }

    public function dealBeforeCreate($product)
    {
        if (empty($this->dealers)) {
            return $product;
        } else {
            foreach ($this->dealers as $dealer) {
                $product = $dealer->dealBeforeCreateProduct($product);
            }

            return $product;
        }
    }

    public function closeOrders($conditions)
    {
        if (empty($conditions['target_type']) || empty($conditions['status'])) {
            return;
        }
        $orderItems = $this->getOrderService()->searchOrderItems($conditions, [], [], PHP_INT_MAX);
        if (empty($orderItems)) {
            return;
        }

        $orderIds = ArrayToolkit::column($orderItems, 'order_id');
        foreach ($orderIds as $orderId) {
            $this->getWorkflowService()->close($orderId);
        }
    }

    private function getTotalDeductExcludeAdjust($deducts)
    {
        $totalDeductAmountExcludeAdjust = 0;
        $adjustDeduct = [];
        foreach ($deducts as $deduct) {
            if (self::DEDUCT_TYPE_ADJUST == $deduct['deduct_type']) {
                $adjustDeduct = $deduct;
            } else {
                $totalDeductAmountExcludeAdjust += $deduct['deduct_amount'];
            }
        }

        return [$totalDeductAmountExcludeAdjust, $adjustDeduct];
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

    /**
     * @return LogService
     */
    private function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
