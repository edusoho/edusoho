<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ResourceNotFoundException;
use ApiBundle\Api\Resource\Resource;

class CourseTrialVideo extends Resource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $courseId)
    {
        $course = $this->service('Course:CourseService')->getCourse($courseId);

        if (!$course) {
            throw new ResourceNotFoundException('教学计划不存在');
        }

        $result = array(
            'trailVideos' => array(),
            'maxWatchLength' => $course['tryLookLength']
        );

        $freeVideoTasks = $this->service('Task:TaskService')->searchTasks(
            array('courseId' => $course['id'], 'type' => 'video', 'isFree' => '1'),
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
            $result['trailVideos'] = $trialVideoTasks;
        } else {
            $result['trailVideos'] = $freeVideoTasks;
        }

        return $result;
    }
}