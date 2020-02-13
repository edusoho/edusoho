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
        $conditions = $this->_prepareConditions($request->query->all());

        list($offset, $limit) = $this->getOffsetAndLimit($request);

        $total = $this->getOpenCourseService()->countLessons($conditions);
        if (!$total) {
            return $this->makePagingObject(array(), $total, $offset, $limit);
        }

        $lessons = $this->getOpenCourseService()->searchLessons($conditions, array('startTime' => 'ASC'), 0, $total);
        $lessons = ArrayToolkit::index($lessons, 'courseId');

        $courseConditions = array(
            'type' => 'liveOpen',
            'status' => 'published',
            'ids' => ArrayToolkit::column($lessons, 'courseId'),
        );

        $total = $this->getOpenCourseService()->countCourses($courseConditions);
        $courses = $this->getOpenCourseService()->searchCourses($courseConditions, array(), $offset, $limit);

        $doingCourses = array();
        $finishedCourses = array();
        $notStartCourses = array();
        foreach ($courses as $course) {
            if (empty($lessons[$course['id']])) {
                continue;
            }

            $course['startTime'] = $lessons[$course['id']]['startTime'];
            $course['lesson'] = $lessons[$course['id']];

            if ($course['lesson']['startTime'] > time()) {
                $notStartCourses[] = $course;
            } elseif ($course['lesson']['startTime'] <= time() && $course['lesson']['endTime'] < time()) {
                $finishedCourses[] = $course;
            } else {
                $doingCourses[] = $course;
            }
        }
        
        $doingCourses = ArrayToolkit::sortPerArrayValue($doingCourses, 'startTime');
        $notStartCourses = ArrayToolkit::sortPerArrayValue($notStartCourses, 'startTime');
        $finishedCourses = ArrayToolkit::sortPerArrayValue($finishedCourses, 'startTime', false);

        return $this->makePagingObject(array_merge($doingCourses, $notStartCourses, $finishedCourses), $total, $offset, $limit);
    }

    protected function _prepareConditions($conditions)
    {
        $preparedConditions = array('type' => 'liveOpen', 'status' => 'published');

        if (!empty($conditions['categoryId'])) {
            $preparedConditions['categoryId'] = $conditions['categoryId'];
        }

        if (!empty($conditions['isReplay'])) {
            $preparedConditions['endTimeLessThan'] = time();
        } elseif (isset($conditions['isReplay'])) {
            $preparedConditions['endTimeGreaterThan'] = time();
        }

        if (!empty($conditions['limitDays']) && is_numeric($conditions['limitDays'])) {
            $preparedConditions['startTimeGreaterThan'] = strtotime(date('Y-m-d', time()));
            $preparedConditions['startTimeLessThan'] = strtotime(date('Y-m-d', time() + $conditions['limitDays'] * 24 * 60 * 60));
        }

        return $preparedConditions;
    }

    /**
     * @return OpenCourseService
     */
    protected function getOpenCourseService()
    {
        return $this->service('OpenCourse:OpenCourseService');
    }
}