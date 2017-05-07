<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\Annotation\ResponseFilter;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\InvalidArgumentException;
use ApiBundle\Api\Exception\ResourceNotFoundException;
use ApiBundle\Api\Resource\AbstractResource;

class CourseTrialTask extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $courseId, $indicator)
    {
        $course = $this->service('Course:CourseService')->getCourse($courseId);

        if (!$course) {
            throw new ResourceNotFoundException('教学计划不存在');
        }

        if ($indicator == 'first') {
            return $this->getFirstTrailTask($course);
        } else {
            throw new InvalidArgumentException();
        }
    }

    private function getFirstTrailTask($course)
    {
        $freeVideoTasks = $this->service('Task:TaskService')->searchTasks(
            array('courseId' => $course['id'], 'isFree' => '1'),
            array('seq' => 'ASC'),
            0,
            1
        );

        if (!$freeVideoTasks && $course['tryLookable'] && $course['tryLookLength'] > 0) {
            $trialVideoTasks = $this->service('Task:TaskService')->searchTasks(
                array('courseId' => $course['id'], 'type' => 'video'),
                array('seq' => 'ASC'),
                0,
                1
            );
            $firstTrailTask = array_pop($trialVideoTasks);
        } else {
            $firstTrailTask = array_pop($freeVideoTasks);
        }

        return $firstTrailTask;
    }
}