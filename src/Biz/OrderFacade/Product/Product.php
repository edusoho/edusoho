<?php

namespace Biz\OrderFacade\Product;

use AppBundle\Common\StringToolkit;
use Biz\AppLoggerConstant;
use Codeages\Biz\Order\Service\OrderService;
use Biz\OrderFacade\Command\Deduct\PickedDeductWrapper;
use Biz\OrderFacade\Currency;
use Biz\Sms\Service\SmsService;
use Biz\System\Service\LogService;
use Codeages\Biz\Framework\Context\BizAware;
use AppBundle\Common\MathToolkit;
use Codeages\Biz\Order\Status\OrderStatusCallback;

abstract class Product extends BizAware implements OrderStatusCallback
{
    /**
     * 商品ID
     *
     * @var int
     */
    public $targetId;

    /**
     * 商品类型
     *
     * @var string
     */
    public $targetType;

    /**
     * 商品名称
     *
     * @var string
     */
    public $title;

    /**
     * 商品原价
     *
     * @var float
     */
    public $originPrice;

    /**
     * 促销价格
     *
     * @var float
     */
    public $promotionPrice = 0;

    /**
     * 可使用的折扣
     *
     * @var array
     */
    public $availableDeducts = array();

    /**
     * 使用到的折扣
     *
     * @var array
     */
    public $pickedDeducts = array();

    /**
     * 返回的链接
     *
     * @var string
     */
    public $backUrl = '';

    /**
     * 成功支付返回链接
     *
     * @var string
     */
    public $successUrl = '';

    /**
     * 最大虚拟币抵扣百分比
     *
     * @var int
     */
    public $maxRate = 100;

    /**
     * 商品数量
     *
     * @var int
     */
    public $num = 1;

    /**
     * 商品单位
     *
     * @var string
     */
    public $unit = '';

    /**
     * 是否可以使用优惠券
     *
     * @var bool
     */
    public $couponEnable = true;

    /**
     * 商品是否可用（如课程、班级被关闭，VIP购买被关闭）
     *
     * @var bool
     */
    public $productEnable = true;

    /**
     * 扩展字段
     */
    private $createExtra;

    /**
     * 封面
     *
     * @var array
     */
    public $cover = array();

    const PRODUCT_VALIDATE_FAIL = '20007';

    abstract public function init(array $params);

    abstract public function validate();

    public function setAvailableDeduct($params = array())
    {
        /** @var $pickedDeductWrapper PickedDeductWrapper */
        $availableDeductWrapper = $this->biz['order.product.available_deduct_wrapper'];

        $availableDeductWrapper->wrapper($this, $params);
    }

    public function setPickedDeduct($params)
    {
        /** @var $pickedDeductWrapper PickedDeductWrapper */
        $pickedDeductWrapper = $this->biz['order.product.picked_deduct_wrapper'];

        $pickedDeductWrapper->wrapper($this, $params);
    }

    public function getPayablePrice()
    {
        $payablePrice = $this->originPrice;
        foreach ($this->pickedDeducts as $deduct) {
            $payablePrice -= $deduct['deduct_amount'];
        }

        return $payablePrice > 0 ? $payablePrice : 0;
    }

    public function getDeducts()
    {
        $deducts = array();
        foreach ($this->pickedDeducts as $deduct) {
            $deducts[$deduct['deduct_type']] = $deduct['deduct_amount'];
        }

        return $deducts;
    }

    public function getMaxCoinAmount()
    {
        return round(($this->maxRate / 100) * $this->getCurrency()->convertToCoin($this->originPrice), 2);
    }

    protected function smsCallback($orderItem, $targetName)
    {
        try {
            $smsType = 'sms_'.$this->targetType.'_buy_notify';

            if ($this->getSmsService()->isOpen($smsType)) {
                $userId = $orderItem['user_id'];
                $parameters = array();
                $parameters['order_title'] = '购买'.$targetName.'-'.$orderItem['title'];
                $parameters['order_title'] = StringToolkit::cutter($parameters['order_title'], 20, 15, 4);
                $price = MathToolkit::simple($orderItem['order']['pay_amount'], 0.01);
                $parameters['totalPrice'] = $price.'元';

                $description = $parameters['order_title'].'成功回执';

                $this->getSmsService()->smsSend($smsType, array($userId), $description, $parameters);
            }
        } catch (\Exception $e) {
            $this->getLogService()->error(AppLoggerConstant::SMS, 'sms_'.$this->targetType.'_buy_notify', "发送短信通知失败:userId:{$orderItem['user_id']}, targetType:{$this->targetType}, targetId:{$this->targetId}", array('error' => $e->getMessage()));
        }
    }

    protected function updateMemberRecordByRefundItem($orderItem)
    {
        $orderRefund = $this->getOrderRefundService()->getOrderRefundById($orderItem['refund_id']);
        $record = array(
            'reason' => $orderRefund['reason'],
            'refund_id' => $orderRefund['id'],
            'reason_type' => 'refund',
        );

        $this->getMemberOperationService()->updateRefundInfoByOrderId($orderRefund['order_id'], $record);
    }

    public function getCreateExtra()
    {
        return empty($this->createExtra) ? array() : $this->createExtra;
    }

    public function setCreateExtra($createExtra)
    {
        $this->createExtra = $createExtra;
    }

    public function getSnapShot()
    {
        return array();
    }

    /**
     * @return Currency
     */
    protected function getCurrency()
    {
        return $this->biz['currency'];
    }

    /**
     * @return SmsService
     */
    private function getSmsService()
    {
        return $this->biz->service('Sms:SmsService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->biz->service('System:LogService');
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->biz->service('Order:OrderService');
    }

    /**
     * @return OrderRefundService
     */
    protected function getOrderRefundService()
    {
        return $this->biz->service('OrderFacade:OrderRefundService');
    }

    protected function getMemberOperationService()
    {
        return $this->biz->service('MemberOperation:MemberOperationService');
    }
}
