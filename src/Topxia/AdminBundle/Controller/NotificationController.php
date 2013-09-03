<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class NotificationController extends BaseController
{

    public function remindCourseTeachersAction(Request $request, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        foreach ($course['teacherIds'] as $receiverId) {
            $result = $this->getNotificationService()->notify($receiverId, 'default',
                "来自后台管理者的提醒，您的课程: << {$course['title']} >> 还有尚未解答的问题,请及时提供答案!");
        }

        return $this->createJsonResponse(array('success' => true, 'message' => 'ok'));
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }
}