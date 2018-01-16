<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\LatestFinishedLearnsDataTag;

class LatestFinishedLearnsDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $courseSet = $this->getCourseSetService()->createCourseSet(array('type' => 'normal', 'title' => 'course set1 title'));

        $course1 = $this->getCourseService()->createCourse(array('title' => 'course title', 'courseSetId' => $courseSet['id'], 'expiryMode' => 'forever', 'learnMode' => 'freeMode', 'courseType' => 'normal'));
        $this->getCourseService()->publishCourse($course1['id']);

        $fields1 = array(
            'title' => 'task1 title',
            'courseId' => $course1['id'],
            'fromCourseSetId' => $course1['courseSetId'],
            'seq' => 1,
            'activityId' => 1,
            'type' => 'text',
            'mode' => '',
            'isFree' => 1,
            'createdUserId' => 1,
        );
        $task1 = $this->getTaskDao()->create($fields1);

        $fields2 = array(
            'title' => 'task1 title',
            'courseId' => $course1['id'],
            'fromCourseSetId' => $course1['courseSetId'],
            'seq' => 2,
            'activityId' => 2,
            'type' => 'text',
            'mode' => '',
            'isFree' => 1,
            'createdUserId' => 1,
        );
        $task2 = $this->getTaskDao()->create($fields2);

        $fields3 = array(
            'title' => 'task1 title',
            'courseId' => $course1['id'],
            'fromCourseSetId' => $course1['courseSetId'],
            'seq' => 3,
            'activityId' => 3,
            'type' => 'text',
            'mode' => '',
            'isFree' => 1,
            'createdUserId' => 1,
        );
        $task3 = $this->getTaskDao()->create($fields3);

        $user = $this->getCurrentuser();

        $result1 = array(
            'activityId' => $task1['activityId'],
            'courseId' => $task1['courseId'],
            'courseTaskId' => $task1['id'],
            'userId' => $user['id'],
        );
        $taskResult1 = $this->getTaskResultService()->createTaskResult($result1);
        $this->getTaskResultService()->updateTaskResult($taskResult1['id'], array('status' => 'finish'));

        $result2 = array(
            'activityId' => $task2['activityId'],
            'courseId' => $task2['courseId'],
            'courseTaskId' => $task2['id'],
            'userId' => $user['id'],
        );
        $taskResult2 = $this->getTaskResultService()->createTaskResult($result2);
        $this->getTaskResultService()->updateTaskResult($taskResult2['id'], array('status' => 'finish'));

        $datatag = new LatestFinishedLearnsDataTag();
        $learns = $datatag->getData(array('count' => 5));
        $this->assertEquals(2, count($learns));

        $learns = $datatag->getData(array());
        $this->assertEquals(2, count($learns));
    }

    private function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    private function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    private function getTaskDao()
    {
        return $this->createDao('Task:TaskDao');
    }

    private function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }
}
