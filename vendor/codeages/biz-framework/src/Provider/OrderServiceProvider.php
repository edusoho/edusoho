<?php

namespace Codeages\Biz\Framework\Provider;

use Codeages\Biz\Framework\Order\Status\Order\ClosedOrderStatus;
use Codeages\Biz\Framework\Order\Status\Order\FailOrderStatus;
use Codeages\Biz\Framework\Order\Status\Order\ConsignedOrderStatus;
use Codeages\Biz\Framework\Order\Status\Order\CreatedOrderStatus;
use Codeages\Biz\Framework\Order\Status\Order\RefundedOrderStatus;
use Codeages\Biz\Framework\Order\Status\Order\RefundingOrderStatus;
use Codeages\Biz\Framework\Order\Status\Order\SuccessOrderStatus;
use Codeages\Biz\Framework\Order\Status\Order\OrderContext;
use Codeages\Biz\Framework\Order\Status\Order\PaidOrderStatus;
use Codeages\Biz\Framework\Order\Status\Order\PayingOrderStatus;
use Codeages\Biz\Framework\Order\Status\Refund\CancelStatus;
use Codeages\Biz\Framework\Order\Status\Refund\RefusedStatus;
use Codeages\Biz\Framework\Order\Status\Refund\CreatedStatus;
use Codeages\Biz\Framework\Order\Status\Refund\RefundedStatus;
use Codeages\Biz\Framework\Order\Status\Refund\OrderRefundContext;
use Codeages\Biz\Framework\Order\Status\Refund\AuditingStatus;
use Codeages\Biz\Framework\Order\Status\Refund\RefundingStatus;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class OrderServiceProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $biz['migration.directories'][] = dirname(dirname(__DIR__)).'/migrations/order';
        $biz['autoload.aliases']['Order'] = 'Codeages\Biz\Framework\Order';

        $this->registerOrderStatus($biz);
        $this->registerOrderRefundStatus($biz);
    }

    private function registerOrderRefundStatus($biz)
    {
        $biz['order_refund_context'] = function ($biz) {
            return new OrderRefundContext($biz);
        };

        $orderRefundStatusArray = array(
            RefundingStatus::class,
            AuditingStatus::class,
            RefusedStatus::class,
            RefundedStatus::class,
            CancelStatus::class
        );

        foreach ($orderRefundStatusArray as $orderRefundStatus) {
            $biz['order_refund_status.'.$orderRefundStatus::NAME] = function ($biz) use ($orderRefundStatus) {
                return new $orderRefundStatus($biz);
            };
        }
    }

    private function registerOrderStatus($biz)
    {
        $biz['order_context'] = function ($biz) {
            return new OrderContext($biz);
        };

        $orderStatusArray = array(
            CreatedOrderStatus::class,
            PayingOrderStatus::class,
            PaidOrderStatus::class,
            FailOrderStatus::class,
            SuccessOrderStatus::class,
            ClosedOrderStatus::class,
            RefundingOrderStatus::class,
            RefundedOrderStatus::class,
        );

        foreach ($orderStatusArray as $orderStatus) {
            $biz['order_status.'.$orderStatus::NAME] = function ($biz) use ($orderStatus) {
                return new $orderStatus($biz);
            };
        }
    }
}
