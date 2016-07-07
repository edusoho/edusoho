<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class ClassroomStatuses extends BaseResource
{
    public function get(Application $app, Request $request, $classroomId)
    {
        $fields = $request->query->all();

        $conditions = array();
        $conditions['userId'] = !empty($fields['userId']) ? $fields['userId'] : $this->getCurrentUser()->id;

        $conditions['classroomId'] = $classroomId;

        if (!empty($fields['courseId'])) {
            $conditions['courseId'] = $fields['courseId'];
        } else {
            // 没有传入courseId参数时，默认取出该用户在班级下所有课程的动态
            $classroomCourses = $this->getClassroomService()->findCoursesByClassroomId($classroomId);
            $classroomCourseIds = ArrayToolkit::column($classroomCourses, 'id');
            $conditions['classroomCourseIds'] = $classroomCourseIds;
        }

        $orderBy = array(
            'createdTime',
            'DESC'
        );

        $start = isset($fields['start']) ? (int) $fields['start'] : 0;
        $limit = isset($fields['limit']) ? (int) $fields['limit'] : 20;

        $statuses = $this->getStatusService()->searchStatuses($conditions, $orderBy, $start, $limit);
        $total = $this->getStatusService()->searchStatusesCount($conditions);

        return $this->wrap($this->filter($statuses), $total);
    }

    public function filter($res)
    {
        return $this->multicallFilter('ClassroomStatus', $res);
    }

    protected function getStatusService()
    {
        return $this->getServiceKernel()->createService('User.StatusService');
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}
