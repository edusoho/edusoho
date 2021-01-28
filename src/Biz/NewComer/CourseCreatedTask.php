<?php

namespace Biz\NewComer;

use Biz\Course\Service\CourseSetService;

class CourseCreatedTask extends BaseNewcomer
{
    public function getStatus()
    {
        $newcomerTask = $this->getSettingService()->get('newcomer_task', array());

        if (!empty($newcomerTask['course_created_task']['status'])) {
            return true;
        }

        $publishCount = $this->getCourseSetService()->countCourseSets(array('status' => 'published'));
        if (!empty($publishCount)) {
            $this->doneTask('course_created_task');

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
