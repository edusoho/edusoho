<?php

namespace AppBundle\Controller\Order;

use AppBundle\Controller\BaseController;
use Biz\Coupon\Service\CouponService;
use Biz\OrderFacade\Product\Product;
use Biz\OrderFacade\Service\OrderFacadeService;
use Symfony\Component\HttpFoundation\Request;

class OrderRefundController extends BaseController
{
    public function refundAction(Request $request, $id)
    {
        $targetType = $request->query->get('targetType');
        $biz = $this->getBiz();

        /* @var $product Product */
        //todo 命名问题
        $product = $biz['order.product.'.$request->query->get('targetType')];
        $fileds = $request->query->all();
        $fileds['targetId'] = $id;
        $product->init($fileds);

        $user = $this->getUser();
        $member = $product->member;
        if (empty($member) || empty($member['orderId'])) {
            throw $this->createAccessDeniedException('您不是学员或尚未购买，不能退学。');
        }

        $order = $this->getOrderService()->getOrder($member['orderId']);
        if (empty($order)) {
            throw $this->createNotFoundException();
        }

        // if ('POST' == $request->getMethod()) {
        //     $data = $request->request->all();
        //     $reason = empty($data['reason']) ? array() : $data['reason'];
        //     $amount = empty($data['applyRefund']) ? 0 : null;

        //     $reason['operator'] = $user['id'];
        //     $refund = $processor->applyRefundOrder($member['orderId'], $amount, $reason, $this->container);

        //     return $this->createJsonResponse(true);
        // }

        $maxRefundDays = (int) (($order['refund_deadline'] - $order['finish_time']) / 86400);

        return $this->render('order-refund/refund-modal.html.twig', array(
            'product' => $product,
            'order' => $order,
            'maxRefundDays' => $maxRefundDays,
        ));
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->getBiz()->service('Order:OrderService');
    }
}