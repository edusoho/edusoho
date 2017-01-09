<?php
namespace Topxia\Service\Course\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Course\CourseMemberService;

class CourseMemberServiceImpl extends BaseService implements CourseMemberService
{

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }

    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

    protected function getLogService()
    {
        return ServiceKernel::instance()->createService('System:LogService');
    }

    protected function getNotificationService()
    {
        return ServiceKernel::instance()->createService('User:NotificationService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}
