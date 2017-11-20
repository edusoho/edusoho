<?php

namespace Codeages\Biz\Order;

use Codeages\Biz\Order\Status\Order\OrderContext;
use Codeages\Biz\Order\Status\Refund\OrderRefundContext;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class OrderServiceProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $biz['migration.directories'][] = dirname(dirname(__DIR__)).'/migrations/order';
        $biz['autoload.aliases']['Order'] = 'Codeages\Biz\Order';

        $this->registerOrderStatus($biz);
        $this->registerOrderRefundStatus($biz);

        $biz['console.commands'][] = function () use ($biz) {
            return new \Codeages\Biz\Order\Command\TableCommand($biz);
        };

        $biz['order.options'] = null;

        $biz['order.final_options'] =  function () use ($biz) {

            $options = array(
                'closed_expired_time' => 2*24*60*60,
            );

            if (!empty($biz['order.options'])) {
                $options = array_merge($options, $biz['order.options']);
            }

            return $options;
        };
    }

    private function registerOrderRefundStatus($biz)
    {
        $biz['order_refund_context'] = function ($biz) {
            return new OrderRefundContext($biz);
        };

        $orderRefundStatusArray = array(
            '\Codeages\Biz\Order\Status\Refund\RefundingStatus',
            '\Codeages\Biz\Order\Status\Refund\AuditingStatus',
            '\Codeages\Biz\Order\Status\Refund\RefusedStatus',
            '\Codeages\Biz\Order\Status\Refund\RefundedStatus',
            '\Codeages\Biz\Order\Status\Refund\CancelStatus',
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
            '\Codeages\Biz\Order\Status\Order\CreatedOrderStatus',
            '\Codeages\Biz\Order\Status\Order\PayingOrderStatus',
            '\Codeages\Biz\Order\Status\Order\PaidOrderStatus',
            '\Codeages\Biz\Order\Status\Order\FailOrderStatus',
            '\Codeages\Biz\Order\Status\Order\SuccessOrderStatus',
            '\Codeages\Biz\Order\Status\Order\ClosedOrderStatus',
            '\Codeages\Biz\Order\Status\Order\RefundingOrderStatus',
            '\Codeages\Biz\Order\Status\Order\RefundedOrderStatus',
            '\Codeages\Biz\Order\Status\Order\FinishedOrderStatus',
        );

        foreach ($orderStatusArray as $orderStatus) {
            $biz['order_status.'.$orderStatus::NAME] = function ($biz) use ($orderStatus) {
                return new $orderStatus($biz);
            };
        }
    }
}
