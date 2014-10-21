<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\StringToolkit;

class CourseOrderController extends BaseController
{

    public function manageAction(Request $request)
    {
        return $this->forward('TopxiaAdminBundle:Order:manage', array(
            'request' => $request,
            'type' => 'course',
            'layout' => 'TopxiaAdminBundle:Course:layout.html.twig',
        ));
    }

    public function refundsAction(Request $request)
    {
        $conditions = $this->prepareRefundSearchConditions($request->query->all());

        $paginator = new Paginator(
            $this->get('request'),
            $this->getOrderService()->searchRefundCount($conditions),
            20
        );

        $refunds = $this->getOrderService()->searchRefunds(
            $conditions,
            'latest',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($refunds, 'userId'));
        $orders = $this->getOrderService()->findOrdersByIds(ArrayToolkit::column($refunds, 'orderId'));

        return $this->render('TopxiaAdminBundle:CourseOrder:refunds.html.twig', array(
            'refunds' => $refunds,
            'users' => $users,
            'orders' => $orders,
            'paginator' => $paginator
        ));
    }

    private function prepareRefundSearchConditions($conditions)
    {
        $conditions = array_filter($conditions);

        if (!empty($conditions['orderSn'])) {
            $order = $this->getOrderService()->getOrderBySn($conditions['orderSn']);
            $conditions['orderId'] = $order ? $order['id'] : -1;
            unset($conditions['orderSn']);
        }

        if (!empty($conditions['nickname'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['nickname']);
            $conditions['userId'] = $user ? $user['id'] : -1;
            unset($conditions['nickname']);
        }

        return $conditions;
    }

    public function cancelRefundAction(Request $request, $id)
    {
        $this->getCourseOrderService()->cancelRefundOrder($id);
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
                if ($this->getCourseService()->isCourseStudent($order['targetId'], $order['userId'])) {
                    $this->getCourseService()->removeStudent($order['targetId'], $order['userId']);
                }
            } else {
                if ($this->getCourseService()->isCourseStudent($order['targetId'], $order['userId'])) {
                    $this->getCourseService()->unlockStudent($order['targetId'], $order['userId']);
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
        $course = $this->getCourseService()->getCourse($order['targetId']);
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

        $courseUrl = $this->generateUrl('course_show', array('id' => $course['id']));
        $variables = array(
            'course' => "<a href='{$courseUrl}'>{$course['title']}</a>",
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

    protected function getCourseOrderService()
    {
        return $this->getServiceKernel()->createService('Course.CourseOrderService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }

}