<?php
namespace Topxia\Service\Search\Adapter;

use Topxia\Common\ArrayToolkit;

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
            $courseLocal = $this->getCourseService()->getCourse($course['courseId']);

            if (!empty($courseLocal)) {
                $course['rating']        = $courseLocal['rating'];
                $course['ratingNum']     = $courseLocal['ratingNum'];
                $course['studentNum']    = $courseLocal['studentNum'];
                $course['middlePicture'] = $courseLocal['middlePicture'];
                $course['learning']      = in_array($course['courseId'], $learningCourseIds);
                $course['id']            = $courseLocal['id'];
            }else{
                $course['rating']        = 0;
                $course['ratingNum']     = 0;
                $course['studentNum']    = 0;
                $course['middlePicture'] = '';
                $course['learning']      = false;
                $course['id']            = $course['courseId'];
            }
            array_push($adaptResult, $course);
        }

        return $adaptResult;
    }

    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }
}
