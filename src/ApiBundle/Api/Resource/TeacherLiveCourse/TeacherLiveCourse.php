<?php

namespace ApiBundle\Api\Resource\TeacherLiveCourse;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;

class TeacherLiveCourse extends AbstractResource
{
    /**
     * @Access(roles="ROLE_TEACHER,ROLE_ADMIN,ROLE_SUPER_ADMIN")
     */
    public function search(ApiRequest $request)
    {
        $conditions = $request->query->all();
        if (empty($conditions['startTime_GE']) || empty($conditions['endTime_LT'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        $user = $this->getCurrentUser();
        $liveCourses = $this->getCourseService()->findLiveCourse($conditions, $user['id'], 'teacher');
        foreach ($liveCourses as &$liveCourse) {
            $liveCourse['url'] = $this->generateUrl('course_task_show', array(
                'courseId' => $liveCourse['courseId'],
                'id' => $liveCourse['taskId'],
            ));
        }
        $openLiveCourses = $this->getOpenCourseService()->findOpenLiveCourse($conditions, $user['id']);
        foreach ($openLiveCourses as &$openLiveCourse) {
            $openLiveCourse['url'] = $this->generateUrl('open_course_show', array(
                'courseId' => $openLiveCourse['id'],
            ));
        }

        return array_merge($liveCourses, $openLiveCourses);
    }

    /**
     * @return OpenCourseService
     */
    private function getOpenCourseService()
    {
        return $this->service('OpenCourse:OpenCourseService');
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }
}
