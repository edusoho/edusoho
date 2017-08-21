<?php

namespace Codeages\Biz\Framework\Provider;

use Codeages\Biz\Framework\Order\Status\Order\ClosedOrderStatus;
use Codeages\Biz\Framework\Order\Status\Order\ConsignedOrderStatus;
use Codeages\Biz\Framework\Order\Status\Order\CreatedOrderStatus;
use Codeages\Biz\Framework\Order\Status\Order\FinishOrderStatus;
use Codeages\Biz\Framework\Order\Status\Order\OrderContext;
use Codeages\Biz\Framework\Order\Status\Order\PaidOrderStatus;
use Codeages\Biz\Framework\Order\Status\Refund\ClosedStatus;
use Codeages\Biz\Framework\Order\Status\Refund\CreatedStatus;
use Codeages\Biz\Framework\Order\Status\Refund\FinishStatus;
use Codeages\Biz\Framework\Order\Status\Refund\OrderRefundContext;
use Codeages\Biz\Framework\Order\Status\Refund\AdoptStatus;
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
            AdoptStatus::class,
            ClosedStatus::class,
            FinishStatus::class
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
            ConsignedOrderStatus::class,
            PaidOrderStatus::class,
            ClosedOrderStatus::class,
            FinishOrderStatus::class
        );

        foreach ($orderStatusArray as $orderStatus) {
            $biz['order_status.'.$orderStatus::NAME] = function ($biz) use ($orderStatus) {
                return new $orderStatus($biz);
            };
        }
    }
}
