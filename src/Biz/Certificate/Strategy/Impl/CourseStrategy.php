<?php

namespace Biz\Certificate\Strategy\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Certificate\Strategy\BaseStrategy;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;

class CourseStrategy extends BaseStrategy
{
    public function getTargetModal($targets)
    {
        return 'admin-v2/operating/certificate/target/course-modal.html.twig';
    }

    public function count($conditions)
    {
        $conditions = $this->filterConditions($conditions);

        return $this->getCourseSetService()->countCourseSets($conditions);
    }

    public function search($conditions, $orderBys, $start, $limit)
    {
        $conditions = $this->filterConditions($conditions);

        $courseSets = $this->getCourseSetService()->searchCourseSets($conditions, $orderBys, $start, $limit);
        if (empty($courseSets)) {
            return [];
        }

        $courseSets = ArrayToolkit::index($courseSets, 'id');
        $courses = $this->getCourseService()->findCoursesByCourseSetIds(ArrayToolkit::column($courseSets, 'id'));
        if (!empty($courses)) {
            foreach ($courses as $course) {
                if ('published' != $course['status']) {
                    continue;
                }

                if (empty($courseSets[$course['courseSetId']]['courses'])) {
                    $courseSets[$course['courseSetId']]['courses'] = [$course];
                } else {
                    $courseSets[$course['courseSetId']]['courses'][] = $course;
                }
            }
        }

        return array_values($courseSets);
    }

    protected function filterConditions($conditions)
    {
        if (!empty($conditions['keyword'])) {
            $conditions['title'] = $conditions['keyword'];
            unset($conditions['keyword']);
        }

        return $conditions;
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->biz->service('Course:CourseSetService');
    }
}
