<?php
namespace Topxia\Service\Order\OrderRefundProcessor;

use Topxia\Service\Order\OrderRefundProcessor\OrderRefundProcessor;
use Topxia\Service\Common\ServiceKernel;

class CourseOrderRefundProcessor implements OrderRefundProcessor
{
	public function getLayout()
	{
		return "TopxiaAdminBundle:Course:layout.html.twig";
	}

	public function findByLikeTitle($title)
	{
		return $this->getCourseService()->findCoursesByLikeTitle($title);
	}

	public function auditRefundOrder($id, $pass, $data)
	{
		$order = $this->getOrderService()->getOrder($id);
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
	}

	public function cancelRefundOrder($id)
	{
		$this->getCourseOrderService()->cancelRefundOrder($id);
	}

	private function sendAuditRefundNotification($order, $pass, $amount, $note)
    {
        $classroom = $this->getClassroomService()->getClassroom($order['targetId']);
        if (empty($classroom)) {
            return false;
        }

        if ($pass) {
            $message = $this->getSettingService()->get('refund.successNotification', '');
        } else {
            $message = $this->getSettingService()->get('refund.failedNotification', '');
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

	protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }

    protected function getNotificationService()
    {
        return ServiceKernel::instance()->createService('User.NotificationService');
    }

    protected function getSettingService()
    {
    	return ServiceKernel::instance()->createService('System.SettingService');
    }

    protected function getOrderService()
    {
        return ServiceKernel::instance()->createService('Order.OrderService');
    }
}