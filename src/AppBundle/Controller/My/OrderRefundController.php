<?php

namespace AppBundle\Controller\My;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use Biz\Order\OrderException;
use Biz\User\UserException;
use Codeages\Biz\Order\Service\OrderRefundService;
use Biz\OrderFacade\Service\OrderRefundService as LocalOrderRefundService;
use Codeages\Biz\Order\Service\OrderService;
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
        $user = $this->getCurrentUser();
        $orderRefund = $this->getOrderRefundService()->getOrderRefundById($id);
        if ($user->getId() != $orderRefund['user_id']) {
            $this->createNewException(UserException::PERMISSION_DENIED());
        }

        $order = $this->getOrderService()->getOrder($orderRefund['order_id']);
        $item = $this->getOrderService()->getOrderItem($orderRefund['order_item_id']);

        return $this->render('my-order/order-refund/detail-modal.html.twig', array(
            'orderRefund' => $orderRefund,
            'order' => $order,
            'item' => $item,
        ));
    }

    public function applyRefundAction(Request $request, $id)
    {
        $user = $this->getCurrentUser();
        $order = $this->getOrderService()->getOrder($id);
        if ($user->getId() != $order['user_id']) {
            $this->createNewException(OrderException::BEYOND_AUTHORITY());
        }
        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();
            $product = $this->getLocalOrderRefundService()->applyOrderRefund($order['id'], $fields);

            return $this->createJsonResponse(array('success' => 1));
        }

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

    /**
     * @return LocalOrderRefundService
     */
    protected function getLocalOrderRefundService()
    {
        return $this->createService('OrderFacade:OrderRefundService');
    }
}
