<?php
namespace Topxia\Service\Search\Adapter;

class CourseSearchAdapter extends AbstractSearchAdapter
{
    public function adapt(array $courses)
    {
        $adaptResult = array();
        $user        = $this->getCurrentUser();

        foreach ($courses as $index => $course) {
            $courseLocal = $this->getCourseService()->getCourse($course['courseId']);

            if (!empty($courseLocal)) {
                $course['rating']        = $courseLocal['rating'];
                $course['ratingNum']     = $courseLocal['ratingNum'];
                $course['studentNum']    = $courseLocal['studentNum'];
                $course['middlePicture'] = $courseLocal['middlePicture'];
                array_push($adaptResult, $course);
            }
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
