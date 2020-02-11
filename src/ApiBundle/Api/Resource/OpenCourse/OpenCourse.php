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
    public function get(ApiRequest $request, $courseId)
    {
        $course = $this->getOpenCourseService()->getCourse($courseId);

        $course['lesson'] = $this->getOpenCourseService()->findLessonsByCourseId($courseId);
        return $course;
    }

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request)
    {
        $conditions = array('type' => 'liveOpen', 'status' => 'published');
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $courses = $this->getOpenCourseService()->searchCourses($conditions, array(), $offset, $limit);
        $courses = ArrayToolkit::index($courses, 'id');
        $total = $this->getOpenCourseService()->countCourses($conditions);

        $conditions['courseIds'] = ArrayToolkit::column($courses, 'id');

        if ($request->query->get('isReplay')) {
            $conditions['endTimeLessThan'] = time();
            $lessons = $this->getOpenCourseService()->searchLessons($conditions, array('startTime' => 'DESC'), 0, count($courses));
        } else {
            $conditions['endTimeGreaterThan'] = time();
            $lessons = $this->getOpenCourseService()->searchLessons($conditions, array('startTime' => 'ASC'), 0, count($courses));
        }

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