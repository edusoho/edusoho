<?php

namespace ApiBundle\Api\Resource\OpenCourse;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\OpenCourse\Service\OpenCourseService;

class OpenCourse extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request)
    {
        $conditions = array('type' => 'liveOpen', 'status' => 'published', 'endTimeGreaterThan' => time());
        $orderBy = array('startTime' => 'ASC');

        if ($request->query->get('categoryId')) {
            $conditions['categoryId'] = $request->query->get('categoryId');
        }

        if ($request->query->get('isReplay')) {
            $conditions['endTimeLessThan'] = time();
            $orderBy = array('startTime' => 'DESC');
            unset($conditions['endTimeGreaterThan']);
        }

        if ($request->query->get('limitDays')) {
            $conditions['endTimeLessThan'] = strtotime('+' . $request->query->get('limitDays') . ' day', strtotime('23:59:59', time()));
        }

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $total = $this->getOpenCourseService()->countLessons($conditions);
        $lessons = $this->getOpenCourseService()->searchLessons($conditions, $orderBy, $offset, $total);

        $conditions = array(
            'type' => 'liveOpen',
            'status' => 'published',
            'ids' => ArrayToolkit::column($lessons, 'courseId'),
        );
        $courses = $this->getOpenCourseService()->searchCourses($conditions, array(), 0, $total);
        $courses = ArrayToolkit::index($courses, 'id');

        $total = $this->getOpenCourseService()->countLessons($conditions);
        $lessons = $this->getOpenCourseService()->searchLessons($conditions, $orderBy, $offset, $total);


        $result = array();
        foreach ($lessons as $lesson) {
            if (empty($courses[$lesson['courseId']])) {
                continue;
            }

            $courses[$lesson['courseId']]['lesson'] = $lesson;
            $result[] = $courses[$lesson['courseId']];
        }

        return $this->makePagingObject($result, $total, $offset, $limit);
    }

    /**
     * @return OpenCourseService
     */
    protected function getOpenCourseService()
    {
        return $this->service('OpenCourse:OpenCourseService');
    }
}