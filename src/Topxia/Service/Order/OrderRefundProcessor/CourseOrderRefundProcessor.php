<?php
namespace Topxia\Service\Order\OrderRefundProcessor;

use Topxia\Service\Order\OrderRefundProcessor\OrderRefundProcessor;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Common\ServiceException;

class CourseOrderRefundProcessor implements OrderRefundProcessor
{
	public function getLayout()
	{
		return "TopxiaAdminBundle:Course:layout.html.twig";
	}

    public function removeStudent($targetId, $userId)
    {
        $this->getCourseService()->removeStudent($targetId, $userId);
    }

    public function getRefundLayout()
    {
        return "TopxiaAdminBundle:Course:refund.layout.html.twig";
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

	}

	public function cancelRefundOrder($id)
	{
		$this->getCourseOrderService()->cancelRefundOrder($id);
	}

    public function getTarget($id)
    {
        return $this->getCourseService()->getCourse($id);
    }

    public function applyRefundOrder($orderId, $amount, $reason, $container)
    {
        return $this->getCourseOrderService()->applyRefundOrder($orderId, $amount, $reason, $container);
    }

    public function getTargetMember($targetId, $userId)
    {
        return $this->getCourseService()->getCourseMember($targetId, $userId);
    }

    protected function getCourseOrderService()
    {
        return ServiceKernel::instance()->createService('Course.CourseOrderService');
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