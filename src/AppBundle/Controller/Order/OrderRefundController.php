<?php

namespace AppBundle\Controller\Order;

use AppBundle\Controller\BaseController;
use Biz\Coupon\Service\CouponService;
use Biz\OrderFacade\Product\Product;
use Biz\OrderFacade\Service\OrderFacadeService;
use Symfony\Component\HttpFoundation\Request;

class OrderRefundController extends BaseController
{
    public function refundAction(Request $request, $orderId)
    {
        $fileds = $request->request->all();
        $product = $this->getOrderRefundService()->applyOrderRefund($orderId, $fileds);
        
        return $this->redirect($this->generateUrl($product->backUrl['routing'], $product->backUrl['params']));
    }

    public function cancelRefund(Request $request, $orderId)
    {
        $user = $this->getCurrentUser();
        $this->getOrderRefundService()->cancelRefund($orderId, $fileds);

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
        return $this->getBiz()->service('OrderRefund:OrderRefundService');
    }
}