<?php

namespace AppBundle\Controller\Product;

use AppBundle\Controller\BaseController;
use Biz\OrderFacade\Product\Product;
use Symfony\Component\HttpFoundation\Request;

class MemberController extends BaseController
{
    function quitMemberAction(Request $request, $targetId, $targetType)
    {
        $biz = $this->getBiz();
        $product = $biz['order.product.'.$targetType];
        $product->init(array('targetId' => $targetId));
        $member = $product->getMember();

        if (empty($member)) {
            throw $this->createNotFoundException();
        }
        $order = $this->getOrderService()->getOrder($member['orderId']);

        if ('POST' == $request->getMethod()) {
            if (!empty($request->request->get('applyRefund'))) {
                return $this->forward('AppBundle:Order/OrderRefund:refund', array(
                    'request' => $request, 
                    'orderId' => $member['orderId'],
                ));
            }
            $product->quitMember();
        } 

        $maxRefundDays = (int) (($order['refund_deadline'] - $order['finish_time']) / 86400);

        return $this->render('product/quit-modal.html.twig', array(
            'order' => $order,
            'product' => $product,
            'maxRefundDays' => $maxRefundDays,
        ));
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }
}