<?php

namespace AppBundle\Controller\My;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use Codeages\Biz\Framework\Order\Service\OrderRefundService;
use Codeages\Biz\Framework\Order\Service\OrderService;
use Symfony\Component\HttpFoundation\Request;

class OrderRefundController extends BaseController
{
    public function indexAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $keyWord = $request->query->get('q');

        $conditions = array(
            'user_id' => $user['id'],
        );
        if (!empty($keyWord)) {
            $conditions['titleLike'] = $keyWord;
        }

        $paginator = new Paginator(
            $request,
            $this->getOrderRefundService()->countRefunds($conditions),
            20
        );

        $orderRefunds = $this->getOrderRefundService()->searchRefunds(
            $conditions,
            array('created_time' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $orderIds = ArrayToolkit::column($orderRefunds, 'order_id');
        $orders = ArrayToolkit::index($this->getOrderService()->findOrdersByIds($orderIds), 'id');

        return $this->render('my-order/order-refund/index.html.twig', array(
            'paginator' => $paginator,
            'orders' => $orders,
            'orderRefunds' => $orderRefunds,
        ));
    }

    public function detailAction(Request $request, $id)
    {
        $orderRefund = $this->getOrderRefundService()->getOrderRefundById($id);
        return $this->render('my-order/order-refund/detail-modal.html.twig', array(
            'orderRefund' => $orderRefund
        ));
    }

    public function applyRefundAction(Request $request, $id)
    {
        $order = $this->getOrderService()->getOrder($id);
        return $this->render('my-order/order-refund/apply-refund-modal.html.twig', array(
            'order' => $order,
        ));
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

    /**
     * @return OrderRefundService
     */
    protected function getOrderRefundService()
    {
        return $this->createService('Order:OrderRefundService');
    }
}
