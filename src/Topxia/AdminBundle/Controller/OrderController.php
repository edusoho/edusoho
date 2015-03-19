<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class OrderController extends BaseController
{
    public function indexAction(Request $request)
    {
        return $this->render('TopxiaAdminBundle:Order:index.html.twig', array(
        ));
    }

    public function manageAction(Request $request, $type, $layout)
    {

        $conditions = $request->query->all();
        $conditions['targetType'] = $type;
        if (isset($conditions['keywordType'])) {
            $conditions[$conditions['keywordType']] = trim($conditions['keyword']);
        }

        $paginator = new Paginator(
            $request,
            $this->getOrderService()->searchOrderCount($conditions),
            20
        );

        $orders = $this->getOrderService()->searchOrders(
            $conditions,
            'latest',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($orders, 'userId'));

        foreach ($orders as $index => $expiredOrderToBeUpdated ){
            if ((($expiredOrderToBeUpdated["createdTime"] + 48*60*60) < time()) && ($expiredOrderToBeUpdated["status"]=='created')){
               $this->getOrderService()->cancelOrder($expiredOrderToBeUpdated['id']);
               $orders[$index]['status'] = 'cancelled';
            }
        }

        return $this->render('TopxiaAdminBundle:Order:manage.html.twig', array(
            'request' => $request,
            'type' => $type,
            'layout' => $layout,
            'orders' => $orders,
            'users' => $users,
            'paginator' => $paginator,
        ));
    }

    public function detailAction(Request $request, $id)
    {
        $order = $this->getOrderService()->getOrder($id);
        $user = $this->getUserService()->getUser($order['userId']);

        $orderLogs = $this->getOrderService()->findOrderLogs($order['id']);

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($orderLogs, 'userId'));

        return $this->render('TopxiaAdminBundle:Order:detail-modal.html.twig', array(
            'order' => $order,
            'user' => $user,
            'orderLogs' => $orderLogs,
            'users' => $users,
        ));
    }

    public function cancelRefundAction(Request $request, $id)
    {
        $this->getClassroomOrderService()->cancelRefundOrder($id);
        return $this->createJsonResponse(true);
    }

    public function auditRefundAction(Request $request, $id)
    {
        $order = $this->getOrderService()->getOrder($id);

        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();

            $pass = $data['result'] == 'pass' ? true : false;
            $this->getOrderService()->auditRefundOrder($order['id'], $pass, $data['amount'], $data['note']);

            if ($pass) {
                if ($this->getClassroomService()->isClassroomStudent($order['targetId'], $order['userId'])) {
                    $this->getClassroomService()->removeStudent($order['targetId'], $order['userId']);
                }
            }

            $this->sendAuditRefundNotification($order, $pass, $data['amount'], $data['note']);

            return $this->createJsonResponse(true);
        }

        return $this->render('TopxiaAdminBundle:CourseOrder:refund-confirm-modal.html.twig', array(
            'order' => $order,
        ));

    }

    private function sendAuditRefundNotification($order, $pass, $amount, $note)
    {
        $course = $this->getClassroomService()->getClassroom($order['targetId']);
        if (empty($course)) {
            return false;
        }

        if ($pass) {
            $message = $this->setting('refund.successNotification', '');
        } else {
            $message = $this->setting('refund.failedNotification', '');
        }

        if (empty($message)) {
            return false;
        }

        $classroomUrl = $this->generateUrl('classroom_show', array('id' => $classroom['id']));
        $variables = array(
            'classroom' => "<a href='{$classroomUrl}'>{$classroom['title']}</a>",
            'amount' => $amount,
            'note' => $note,
        );
        
        $message = StringToolkit::template($message, $variables);
        $this->getNotificationService()->notify($order['userId'], 'default', $message);

        return true;
    }

    protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }
}
