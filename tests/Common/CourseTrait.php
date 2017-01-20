<?php

namespace Tests\Common;

trait CourseTrait
{
    public function createCourse($customFields = array())
    {
        $defaultFields = array(
            'title' => 'course 1',
            'courseSetId' => 1,
            'expiryMode' => '',
            'learnMode' => 'freeMode',
        );

        $fields = array_merge($defaultFields, $customFields);
        return $this->getCourseService()->createCourse($fields);
    }

    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }
}
