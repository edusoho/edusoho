<?php

namespace AppBundle\Controller\Product;

use AppBundle\Controller\BaseController;
use Biz\OrderFacade\Product\Product;
use Symfony\Component\HttpFoundation\Request;

class OwnerController extends BaseController
{
    public function exitAction(Request $request, $targetId, $targetType)
    {
        $user = $this->getUser();

        $params = array('targetId' => $targetId);
        $product = $this->getOrderFacadeService()->getOrderProduct($targetType, $params);

        $member = $product->getOwner($user['id']);

        if (empty($member)) {
            throw $this->createNotFoundException();
        }
        $order = $this->getOrderService()->getOrder($member['orderId']);

        if ('POST' == $request->getMethod()) {
            if ($request->request->get('applyRefund')) {
                return $this->forward('AppBundle:Order/OrderRefund:refund', array(
                    'request' => $request,
                    'orderId' => $member['orderId'],
                ));
            }
            $product->exitOwner($request->request->all());

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

    protected function getOrderFacadeService()
    {
        return $this->createService('OrderFacade:OrderFacadeService');
    }
}
