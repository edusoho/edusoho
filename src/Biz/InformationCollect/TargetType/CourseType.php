<?php

namespace Biz\InformationCollect\TargetType;

use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;

class CourseType extends TargetType
{
    public function getTargetInfo($targetIds)
    {
        $courses = $this->getCourseService()->findCoursesByIds($targetIds);
        $courses = ArrayToolkit::column($courses, 'courseSetTitle');

        return implode('；', $courses);
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}
