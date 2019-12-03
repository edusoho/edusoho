<?php

namespace Biz\NewComer;

use Biz\Course\Service\CourseSetService;

class CourseCreatedTask extends BaseNewcomer
{
    public function getStatus()
    {
        $publishCount = $this->getCourseSetService()->countCourseSets(array('status' => 'published'));
        if (!empty($publishCount)) {
            return true;
        }

        return false;
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }
}
