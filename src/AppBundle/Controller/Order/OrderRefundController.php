<?php

namespace AppBundle\Controller\Order;

use AppBundle\Controller\BaseController;
use Biz\OrderFacade\Service\OrderRefundService;
use Biz\OrderRefund\Service\OrderRefundProxyService;
use Codeages\Biz\Framework\Order\Service\OrderService;
use Symfony\Component\HttpFoundation\Request;

class OrderRefundController extends BaseController
{
    public function refundAction(Request $request, $orderId)
    {
        $fields = $request->request->all();
        $fields['reason'] = $fields['reason']['note'];
        $product = $this->getOrderRefundService()->applyOrderRefund($orderId, $fields);

        return $this->redirect($this->generateUrl($product->backUrl['routing'], $product->backUrl['params']));
    }

    public function cancelRefundAction(Request $request, $orderId)
    {
        $user = $this->getCurrentUser();
        $this->getOrderRefundService()->cancelRefund($orderId);

        return $this->createJsonResponse(true);
    }

    private function canApplyOrderRefund($order)
    {
        return ($order['pay_amount'] > 0) && ($order['refund_deadline'] > time());
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->getBiz()->service('Order:OrderService');
    }

    /**
     * @return OrderRefundService
     */
    protected function getOrderRefundService()
    {
        return $this->getBiz()->service('OrderFacade:OrderRefundService');
    }
}
