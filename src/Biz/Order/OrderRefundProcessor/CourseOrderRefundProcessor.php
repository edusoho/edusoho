<?php

namespace Biz\Order\OrderRefundProcessor;

use Topxia\Service\Common\ServiceKernel;

class CourseOrderRefundProcessor implements OrderRefundProcessor
{
    public function removeStudent($targetId, $userId)
    {
        $this->getCourseMemberService()->removeStudent($targetId, $userId);
    }

    public function findByLikeTitle($title)
    {
        $conditions = array(
            'title' => $title,
        );

        return $this->getCourseService()->searchCourses($conditions, null, 0, PHP_INT_MAX);
    }

    public function auditRefundOrder($id, $pass, $data)
    {
        $order = $this->getOrderService()->getOrder($id);
        if ($pass) {
            if ($this->getCourseMemberService()->isCourseStudent($order['targetId'], $order['userId'])) {
                $this->getCourseMemberService()->removeStudent($order['targetId'], $order['userId']);
            }
        } else {
            if ($this->getCourseMemberService()->isCourseStudent($order['targetId'], $order['userId'])) {
                $this->getCourseMemberService()->unlockStudent($order['targetId'], $order['userId']);
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
        return $this->getCourseMemberService()->getCourseMember($targetId, $userId);
    }

    protected function getCourseOrderService()
    {
        return ServiceKernel::instance()->createService('Course:CourseOrderService');
    }

    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course:CourseService');
    }

    protected function getNotificationService()
    {
        return ServiceKernel::instance()->createService('User:NotificationService');
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System:SettingService');
    }

    protected function getOrderService()
    {
        return ServiceKernel::instance()->createService('Order:OrderService');
    }

    protected function getCourseMemberService()
    {
        return ServiceKernel::instance()->createService('Course:MemberService');
    }
}
