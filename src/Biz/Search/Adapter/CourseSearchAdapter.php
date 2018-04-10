<?php

namespace Biz\Search\Adapter;

use AppBundle\Common\ArrayToolkit;

class CourseSearchAdapter extends AbstractSearchAdapter
{
    public function adapt(array $courses)
    {
        $adaptResult = array();
        $user = $this->getCurrentUser();

        $learningCourseIds = array();

        $tasks = array();

        if (!empty($user['id'])) {
            $courseIds = ArrayToolkit::column($courses, 'courseId');
            $plans = $this->getCourseService()->findCoursesByCourseSetIds($courseIds);
            $planIds = ArrayToolkit::column($plans, 'id');
            $tasks = $this->getCourseTaskService()->findTasksByCourseIds($planIds);
            $tasks = ArrayToolkit::group($tasks, 'fromCourseSetId');
            $learningCourse = $this->getCourseMemberService()->findCoursesByStudentIdAndCourseIds($user['id'], $planIds);
            $learningCourseIds = ArrayToolkit::column($learningCourse, 'courseSetId');
        }

        foreach ($courses as $index => $course) {
            if ($this->isOpenCourse($course)) {
                $course = $this->adaptOpenCourse($course);
            } else {
                $course = $this->adaptCourse($course, $learningCourseIds, $tasks);
            }

            array_push($adaptResult, $course);
        }

        return $adaptResult;
    }

    protected function adaptCourse($course, $learningCourseIds, $tasks)
    {
        //兼容老的模式，CourseSet映射到云搜索的Course资源，task映射到云搜索的lesson资源
        $courseLocal = $this->getCourseSetService()->getCourseSet($course['courseId']);

        if (!empty($courseLocal)) {
            $course['rating'] = $courseLocal['rating'];
            $course['ratingNum'] = $courseLocal['ratingNum'];
            $course['studentNum'] = $courseLocal['studentNum'];
            $course['middlePicture'] = isset($courseLocal['cover']['middle']) ? $courseLocal['cover']['middle'] : '';
            $course['learning'] = in_array($course['courseId'], $learningCourseIds);
            $course['id'] = $courseLocal['defaultCourseId'];
            $course['lessons'] = isset($tasks[$course['courseId']]) ? $tasks[$course['courseId']] : array();
        } else {
            $course['rating'] = 0;
            $course['ratingNum'] = 0;
            $course['studentNum'] = 0;
            $course['middlePicture'] = '';
            $course['learning'] = false;
            $course['id'] = $course['courseId'];
            $course['about'] = '';
        }

        return $course;
    }

    protected function adaptOpenCourse($openCourse)
    {
        $local = $this->getOpenCourseService()->getCourse($openCourse['courseId']);

        if (!empty($local)) {
            $openCourse['id'] = $local['id'];
            $openCourse['middlePicture'] = $local['middlePicture'];
        } else {
            $openCourse['id'] = $openCourse['courseId'];
            $openCourse['middlePicture'] = '';
        }

        return $openCourse;
    }

    protected function isOpenCourse($course)
    {
        return 0 === strpos($course['type'], 'public_');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return \Biz\Course\Service\CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return \Biz\Task\Service\TaskService
     */
    protected function getCourseTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    protected function getOpenCourseService()
    {
        return $this->createService('OpenCourse:OpenCourseService');
    }

    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }
}
