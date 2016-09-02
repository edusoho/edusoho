<?php
namespace Topxia\Service\Search\Adapter;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\OpenCourse\Impl\OpenCourseServiceImpl;

class CourseSearchAdapter extends AbstractSearchAdapter
{
    public function adapt(array $courses)
    {
        $adaptResult = array();
        $user        = $this->getCurrentUser();

        $learningCourseIds = array();

        if (!empty($user['id'])) {
            $courseIds         = ArrayToolkit::column($courses, 'courseId');
            $learningCourse    = $this->getCourseService()->findCoursesByStudentIdAndCourseIds($user['id'], $courseIds);
            $learningCourseIds = ArrayToolkit::column($learningCourse, 'courseId');
        }

        foreach ($courses as $index => $course) {
            if ($this->isOpenCourse($course)) {
                $course = $this->adaptOpenCourse($course);
            } else {
                $course = $this->adaptCourse($course, $learningCourseIds);
            }

            array_push($adaptResult, $course);
        }

        return $adaptResult;
    }

    protected function adaptCourse($course, $learningCourseIds)
    {
        $courseLocal = $this->getCourseService()->getCourse($course['courseId']);

        if (!empty($courseLocal)) {
            $course['rating']        = $courseLocal['rating'];
            $course['ratingNum']     = $courseLocal['ratingNum'];
            $course['studentNum']    = $courseLocal['studentNum'];
            $course['middlePicture'] = $courseLocal['middlePicture'];
            $course['learning']      = in_array($course['courseId'], $learningCourseIds);
            $course['id']            = $courseLocal['id'];
        } else {
            $course['rating']        = 0;
            $course['ratingNum']     = 0;
            $course['studentNum']    = 0;
            $course['middlePicture'] = '';
            $course['learning']      = false;
            $course['id']            = $course['courseId'];
        }

        return $course;
    }

    protected function adaptOpenCourse($openCourse)
    {
        $local = $this->getOpenCourseService()->getCourse($openCourse['courseId']);

        if (!empty($local)) {
            $openCourse['id']            = $local['id'];
            $openCourse['middlePicture'] = $local['middlePicture'];
        } else {
            $openCourse['id']            = $openCourse['courseId'];
            $openCourse['middlePicture'] = '';
        }

        return $openCourse;
    }

    protected function isOpenCourse($course)
    {
        return strpos($course['type'], 'public_') === 0;
    }

    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

    /**
     * @return OpenCourseServiceImpl
     */
    protected function getOpenCourseService()
    {
        return $this->createService('OpenCourse.OpenCourseService');
    }
}
