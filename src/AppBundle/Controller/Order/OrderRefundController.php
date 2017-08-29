<?php

namespace AppBundle\Controller\Order;

use AppBundle\Controller\BaseController;
use Biz\Coupon\Service\CouponService;
use Biz\OrderFacade\Product\Product;
use Biz\OrderFacade\Service\OrderFacadeService;
use Symfony\Component\HttpFoundation\Request;

class OrderController extends BaseController
{
    public function refundAction(Request $request, $id)
    {
        $targetType = $request->query->get('targetType');
        $product = $this->getProduct($request->query->get('targetType'), $request->query->all());

        $user = $this->getUser();
        $member = $processor->getTargetMember($id, $user['id']);

        if (empty($member) || empty($member['orderId'])) {
            throw $this->createAccessDeniedException('您不是学员或尚未购买，不能退学。');
        }

        $order = $this->getOrderService()->getOrder($member['orderId']);

        if (empty($order)) {
            throw $this->createNotFoundException();
        }

        if ('POST' == $request->getMethod()) {
            $data = $request->request->all();
            $reason = empty($data['reason']) ? array() : $data['reason'];
            $amount = empty($data['applyRefund']) ? 0 : null;

            $reason['operator'] = $user['id'];
            $refund = $processor->applyRefundOrder($member['orderId'], $amount, $reason, $this->container);

            return $this->createJsonResponse(true);
        }

        $maxRefundDays = (int) (($order['refundEndTime'] - $order['paidTime']) / 86400);

        return $this->render('order-refund/refund-modal.html.twig', array(
            'target' => $target,
            'targetType' => $targetType,
            'order' => $order,
            'maxRefundDays' => $maxRefundDays,
        ));
    }
}