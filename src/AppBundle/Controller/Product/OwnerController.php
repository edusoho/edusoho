<?php

namespace AppBundle\Controller\Product;

use AppBundle\Controller\BaseController;
use Biz\OrderFacade\Product\Product;
use Symfony\Component\HttpFoundation\Request;

class OwnerController extends BaseController
{
    public function exitAction(Request $request, $targetId, $targetType)
    {
        $biz = $this->getBiz();
        $user = $this->getUser();
        $product = $biz['order.product.'.$targetType];
        $product->init(array('targetId' => $targetId));
        $userId = $user->getId();
        $member = $product->getOwner($userId);

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
            $product->exitOwner($userId);

            return $this->redirect($this->generateUrl($product->backUrl['routing'], $product->backUrl['params']));
        }

        $maxRefundDays = (int) (($member['refundDeadline'] - $member['createdTime']) / 86400);

        return $this->render('product/exit-modal.html.twig', array(
            'order' => $order,
            'product' => $product,
            'member' => $member,
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
