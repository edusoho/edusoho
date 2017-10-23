<?php

namespace Codeages\Biz\Framework\Provider;

use Codeages\Biz\Framework\Order\Status\Order\OrderContext;
use Codeages\Biz\Framework\Order\Status\Refund\OrderRefundContext;
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

        $biz['console.commands'][] = function () use ($biz) {
            return new \Codeages\Biz\Framework\Order\Command\TableCommand($biz);
        };

    }

    private function registerOrderRefundStatus($biz)
    {
        $biz['order_refund_context'] = function ($biz) {
            return new OrderRefundContext($biz);
        };

        $orderRefundStatusArray = array(
            '\Codeages\Biz\Framework\Order\Status\Refund\RefundingStatus',
            '\Codeages\Biz\Framework\Order\Status\Refund\AuditingStatus',
            '\Codeages\Biz\Framework\Order\Status\Refund\RefusedStatus',
            '\Codeages\Biz\Framework\Order\Status\Refund\RefundedStatus',
            '\Codeages\Biz\Framework\Order\Status\Refund\CancelStatus',
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
            '\Codeages\Biz\Framework\Order\Status\Order\CreatedOrderStatus',
            '\Codeages\Biz\Framework\Order\Status\Order\PayingOrderStatus',
            '\Codeages\Biz\Framework\Order\Status\Order\PaidOrderStatus',
            '\Codeages\Biz\Framework\Order\Status\Order\FailOrderStatus',
            '\Codeages\Biz\Framework\Order\Status\Order\SuccessOrderStatus',
            '\Codeages\Biz\Framework\Order\Status\Order\ClosedOrderStatus',
            '\Codeages\Biz\Framework\Order\Status\Order\RefundingOrderStatus',
            '\Codeages\Biz\Framework\Order\Status\Order\RefundedOrderStatus',
        );

        foreach ($orderStatusArray as $orderStatus) {
            $biz['order_status.'.$orderStatus::NAME] = function ($biz) use ($orderStatus) {
                return new $orderStatus($biz);
            };
        }
    }
}
