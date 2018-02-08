<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\Resource\Course\CourseMemberFilter;
use ApiBundle\Api\Resource\Course\CourseTaskFilter;
use ApiBundle\Api\Resource\Filter;

class MeCourseLearningProgressFilter extends Filter
{
    protected $publicFields = array(
        'taskCount', 'toLearnTasks', 'progress', 'taskResultCount', 'taskPerDay', 'planStudyTaskCount', 'planProgressProgress', 'member',
    );

    protected function publicFields(&$data)
    {
        if ($data['toLearnTasks'] && $this->getNextToLearnTask($data)) {
            $courseTaskFilter = new CourseTaskFilter();
            $courseTaskFilter->setMode(Filter::SIMPLE_MODE);
            $courseTaskFilter->filter($data['nextTask']);
        } else {
            $data['nextTask'] = new \stdClass();
        }

        unset($data['toLearnTasks']);

        $courseMemberFilter = new CourseMemberFilter();
        $courseMemberFilter->setMode(Filter::SIMPLE_MODE);
        $courseMemberFilter->filter($data['member']);
    }

    private function getNextToLearnTask(&$data)
    {
        $nextTask = new \stdClass();
        foreach ($data['toLearnTasks'] as $task) {
            if (!empty($task['result']) && $task['result']['status'] == 'finish') {
                continue;
            } else {
                $nextTask = $task;
                break;
            }
        }
        $data['nextTask'] = $nextTask;

        return is_array($data['nextTask']);
    }
}
