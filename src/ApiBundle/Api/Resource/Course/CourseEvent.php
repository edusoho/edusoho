<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\Service\CourseService;
use Codeages\Biz\Framework\Event\Event;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
            throw new NotFoundHttpException('计划不存在', null, ErrorCode::RESOURCE_NOT_FOUND);
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
