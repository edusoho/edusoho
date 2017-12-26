<?php

namespace ApiBundle\Api\Resource\StudentLiveCourse;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use ApiBundle\Api\Exception\ErrorCode;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class StudentLiveCourse extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request)
    {
        $conditions = $request->query->all();
        if (empty($conditions['createdTime_GE']) || empty($conditions['createdTime_LT'])) {
            throw new BadRequestHttpException('Params missing', null, ErrorCode::INVALID_ARGUMENT);
        }
        $user = $this->getCurrentUser();
        $liveCourses = $this->getCourseService()->findLiveCourse($conditions, $user['id'], 'student');
        foreach ($liveCourses as &$liveCourse) {
            $liveCourse['url'] = $this->generateUrl('course_task_show', array(
                'courseId' => $liveCourse['courseId'],
                'id' => $liveCourse['taskId']
            ));
        }
        return array('data' => $liveCourses);
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }
}