<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class ClassroomStatus extends BaseResource
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

        return $this->_filterStatus($statuses);
    }

    public function filter($res)
    {
        $res = ArrayToolkit::parts($res, array('id', 'userId', 'courseId', 'classroomId', 'type', 'objectType', 'objectId', 'properties', 'createdTime'));

        return $res;
    }

    private function _filterStatus(&$res)
    {
        foreach ($res as $key => &$item) {
            unset($item['private']);
            unset($item['commentNum']);
            unset($item['likeNum']);
        }

        return $res;
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
