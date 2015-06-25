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

    public function getRefundLayout()
    {
        return "ClassroomBundle:ClassroomAdmin:refund.layout.html.twig";
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

    public function removeStudent($targetId, $userId)
    {
        $this->getClassroomService()->removeStudent($targetId, $userId);
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

	}

	public function cancelRefundOrder($id)
	{
		$this->getClassroomOrderService()->cancelRefundOrder($id);
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