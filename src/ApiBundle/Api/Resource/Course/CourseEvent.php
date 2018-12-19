<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\CourseException;
use Biz\Course\Service\CourseService;
use Codeages\Biz\Framework\Event\Event;

class CourseEvent extends AbstractResource
{
    const COURSE_VIEW = 'course_view';

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function update(ApiRequest $request, $courseId, $event)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if (!$course) {
            throw CourseException::NOTFOUND_COURSE();
        }

        if ($event == 'course_view') {
            $this->getCourseService()->hitCourse($courseId);
            $this->dispatchEvent('course.view', new Event($course, array('userId' => $this->getCurrentUser()->getId())));
        }

        return array('success' => 1);
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }
}
