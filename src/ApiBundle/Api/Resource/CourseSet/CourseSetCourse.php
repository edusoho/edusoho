<?php

namespace ApiBundle\Api\Resource\CourseSet;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\CourseSetException;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;

class CourseSetCourse extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $courseSetId)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);

        if (!$courseSet) {
            throw CourseSetException::NOTFOUND_COURSESET();
        }

        $courses = $this->getCourseService()->findPublishedCoursesByCourseSetId($courseSetId);
        $courses = ArrayToolkit::sortPerArrayValue($courses, 'seq');

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
            $course['isAudioOn'] = $enableAudioStatus ? '1' : '0';
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
