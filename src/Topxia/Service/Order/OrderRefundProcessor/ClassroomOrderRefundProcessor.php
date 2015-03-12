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
        $conditions = array(
            'title' => $title
        );
		return $this->getClassroomService()->searchClassrooms(
            $conditions, 
            array('createdTime','desc'),
            0,
            100
        );
	}

	public function auditRefundOrder($id, $pass, $data)
	{
		$order = $this->getOrderService()->getOrder($id);

        if ($pass) {
            if ($this->getClassroomService()->isClassroomStudent($order['targetId'], $order['userId'])) {
                $this->getClassroomService()->exitClassroom($order['targetId'], $order['userId']);
            }
        } else {
            if ($this->getClassroomService()->isClassroomStudent($order['targetId'], $order['userId'])) {
                $this->getClassroomService()->unlockStudent($order['targetId'], $order['userId']);
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

    public function getTarget($id)
    {
        return $this->getClassroomService()->getClassroom($id);
    }

    public function applyRefundOrder($orderId, $amount, $reason, $container)
    {
        return $this->getClassroomOrderService()->applyRefundOrder($orderId, $amount, $reason, $container);
    }

    public function getTargetMember($targetId, $userId)
    {
        return $this->getClassroomService()->getClassroomMember($targetId, $userId);
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