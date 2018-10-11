<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;
use Biz\Course\CourseException;

class CourseTrialTask extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $courseId, $indicator)
    {
        $course = $this->service('Course:CourseService')->getCourse($courseId);

        if (!$course) {
            throw CourseException::NOTFOUND_COURSE();
        }

        if ($indicator == 'first') {
            return $this->getFirstTrailTask($course);
        } else {
            throw CommonException::ERROR_PARAMETER();
        }
    }

    private function getFirstTrailTask($course)
    {
        $freeVideoTasks = $this->service('Task:TaskService')->searchTasks(
            array('courseId' => $course['id'], 'isFree' => '1', 'status' => 'published'),
            array('seq' => 'ASC'),
            0,
            1
        );

        if (!$freeVideoTasks && $course['tryLookable'] && $course['tryLookLength'] > 0) {
            $trialVideoTasks = $this->service('Task:TaskService')->searchTasks(
                array('courseId' => $course['id'], 'type' => 'video', 'status' => 'published'),
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
