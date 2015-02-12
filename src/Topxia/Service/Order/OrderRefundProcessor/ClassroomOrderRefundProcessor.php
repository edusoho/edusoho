<?php
namespace Topxia\Service\Order\OrderRefundProcessor;

use Topxia\Service\Order\OrderRefundProcessor\OrderRefundProcessor;
use Topxia\Service\Common\ServiceKernel;

class ClassroomOrderRefundProcessor implements OrderRefundProcessor
{
	public function getLayout()
	{
		return 'ClassroomBundle:ClassroomAdmin:layout.html.twig';
	}

	public function findByLikeTitle($title)
	{
		return $this->getClassroomService()->findClassroomsByLikeTitle($title);
	}

	public function auditRefundOrder($id, $pass, $data)
	{
		$order = $this->getOrderService()->getOrder($id);
		if ($pass) {
            if ($this->getClassroomService()->isClassroomStudent($order['targetId'], $order['userId'])) {
                $this->getClassroomService()->exitClassroom($order['targetId'], $order['userId']);
            }
        }

        $this->sendAuditRefundNotification($order, $pass, $data['amount'], $data['note']);

	}

	public function cancelRefundOrder($id)
	{
		$this->getClassroomOrderService()->cancelRefundOrder($id);
	}

	private function sendAuditRefundNotification($order, $pass, $amount, $note)
    {
        $course = $this->getCourseService()->getCourse($order['targetId']);
        if (empty($course)) {
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

    protected function getClassroomService()
    {
        return ServiceKernel::instance()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getClassroomOrderService()
    {
        return ServiceKernel::instance()->createService('Classroom:Classroom.ClassroomOrderService');
    }
}