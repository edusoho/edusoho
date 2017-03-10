<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use AppBundle\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;

class Status extends BaseResource
{
    public function get(Application $app, Request $request, $courseId)
    {
        try {
            list($course, $member) = $this->getCourseService()->tryTakeCourse($courseId);
        } catch (\Exception $e) {
            return $this->error(404, "用户尚未登录或不是课程学员");
        }

        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 10);

        $statuses = $this->getStatusService()->searchStatuses(
            array('userId' => $member['userId'], 'courseId' => $courseId),
            array('createdTime' => 'DESC'),
            $start,
            $limit
        );

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
        return ServiceKernel::instance()->createService('User:StatusService');
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }
}
