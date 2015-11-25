<?php
namespace Topxia\Service\Search\Adapter;

class CourseSearchAdapter extends AbstractSearchAdapter
{
    public function adapt(array $courses)
    {
        $adaptResult = array();

        foreach ($courses as $index => $course) {
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
