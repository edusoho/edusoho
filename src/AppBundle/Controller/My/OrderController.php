<?php

namespace AppBundle\Controller\My;

use Biz\Order\OrderRefundProcessor\OrderRefundProcessorFactory;
use Biz\Order\Service\OrderService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\BaseController;

class OrderController extends BaseController
{
    protected function getTimeRange($fields)
    {
        if (isset($fields['startTime']) && isset($fields['endTime']) && $fields['startTime'] != '' && $fields['endTime'] != '') {
            if ($fields['startTime'] > $fields['endTime']) {
                return false;
            }

            return array('startTime' => strtotime($fields['startTime']), 'endTime' => (strtotime($fields['endTime']) + 24 * 3600));
        }

        return array('startTime' => strtotime(date('Y-m', time())), 'endTime' => strtotime(date('Y-m-d', time() + 24 * 3600)));
    }

    public function indexAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $status = $request->get('status');

        $conditions = array(
            'userId' => $user['id'],
            'status' => $request->get('status'),
            'title' => $request->get('q'),
        );

        $paginator = new Paginator(
            $request,
            $this->getOrderService()->countOrders($conditions),
            20
        );

        $createdOrderCount = $this->getOrderService()->countOrders(array('userId' => $user['id'], 'status' => 'created'));
        $orders = $this->getOrderService()->searchOrders(
            $conditions,
            'latest',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('my-order/index.html.twig', array(
            'orders' => $orders,
            'paginator' => $paginator,
            'request' => $request,
            'createdOrderCount' => $createdOrderCount,
        ));
    }

    public function detailAction(Request $request, $id)
    {
        $currentUser = $this->getCurrentUser();
        $order = $this->tryManageOrder($id);

        $user = $this->getUserService()->getUser($order['userId']);

        $orderLogs = $this->getOrderService()->findOrderLogs($order['id']);

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($orderLogs, 'userId'));

        return $this->render('my-order/detail-modal.html.twig', array(
            'order' => $order,
            'user' => $user,
            'orderLogs' => $orderLogs,
            'users' => $users,
        ));
    }

    public function cancelRefundAction(Request $request, $id)
    {
        $order = $this->tryManageOrder($id);
        $processor = OrderRefundProcessorFactory::create($order['targetType']);
        $processor->cancelRefundOrder($id);

        return $this->createJsonResponse(true);
    }

    public function cancelAction(Request $request, $id)
    {
        $this->tryManageOrder($id);
        $order = $this->getOrderService()->cancelOrder($id, '取消订单');

        return $this->createJsonResponse(true);
    }

    protected function tryManageOrder($id)
    {
        $currentUser = $this->getCurrentUser();
        $order = $this->getOrderService()->getOrder($id);

        if ($currentUser['id'] != $order['userId']) {
            throw $this->createAccessDeniedException('该订单不属于当前登录用户');
        }

        return $order;
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->getBiz()->service('Order:OrderService');
    }

    /**
     * @return CourseOrderService
     */
    protected function getCourseOrderService()
    {
        return $this->getBiz()->service('Course:CourseOrderService');
    }
}
