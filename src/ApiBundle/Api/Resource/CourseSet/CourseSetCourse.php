<?php

namespace ApiBundle\Api\Resource\CourseSet;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CourseSetCourse extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $courseSetId)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);

        if (!$courseSet) {
            throw new NotFoundHttpException('课程不存在', null, ErrorCode::RESOURCE_NOT_FOUND);
        }

        $courses = $this->getCourseService()->findPublishedCoursesByCourseSetId($courseSetId);

        $this->getOCUtil()->multiple($courses, array('creator', 'teacherIds'));
        $this->getOCUtil()->multiple($courses, array('courseSetId'), 'courseSet');

        $this->addAccessAttr($courses);

        return $courses;
    }

    private function addAccessAttr(&$courses)
    {
        foreach ($courses as &$course) {
            $course['access'] = $this->getCourseService()->canJoinCourse($course['id']);
            
            $enableAudioStatus = $this->getCourseService()->isSupportEnableAudio($course['enableAudio']);
            $course['isAudioOn'] = $enableAudioStatus ? 1 : 0;
            unset($course['enableAudio']);
        }
    }

    /**
     * @return CourseSetService
     */
    private function getCourseSetService()
    {
        return $this->service('Course:CourseSetService');
    }
    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }
}