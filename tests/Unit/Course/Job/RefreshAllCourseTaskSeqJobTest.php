<?php

namespace Tests\Unit\Course\Job;

use Biz\BaseTestCase;
use Biz\Course\Job\RefreshAllCourseTaskSeqJob;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Task\Strategy\Impl\DefaultStrategy;
use Biz\Task\Strategy\Impl\NormalStrategy;

class RefreshAllCourseTaskSeqJobTest extends BaseTestCase
{
    public function testExecute()
    {
        $courseSet = $this->createNewCourseSet();
        $course = $this->defaultCourse('第一个教学计划', array('id' => $courseSet['id']));

        $this->getCourseService()->createCourse($course);
        $mockObj = \Mockery::mock('Biz\Task\Strategy\Impl\NormalStrategy');
        $mockObj->shouldReceive('accept')
            ->andReturn(true);
        $this->biz['course.normal_strategy'] = $mockObj;

        $job = new RefreshAllCourseTaskSeqJob(array(), $this->getBiz());
        $return = $job->execute();
        $this->assertNull($return);
    }

    public function testExecuteDefault()
    {
        $courseSet = $this->createNewCourseSet();
        $course = $this->defaultCourse('第一个教学计划', array('id' => $courseSet['id']));
        $course['courseType'] = 'default';

        $course = $this->getCourseService()->createCourse($course);
        $this->createTask('default', $course['id']);
        $mockObj = \Mockery::mock('Biz\Task\Strategy\Impl\DefaultStrategy');
        $mockObj->shouldReceive('accept')
            ->andReturn(true);
        $this->biz['course.default_strategy'] = $mockObj;
        $job = new RefreshAllCourseTaskSeqJob(array(), $this->getBiz());
        $return = $job->execute();
        $this->assertNull($return);
    }

    protected function createTask($type, $courseId)
    {
        $field = array(
            'mode' => 'lesson',
            'fromCourseId' => $courseId,
            'title' => 'task title',
            'seq' => '1',
            'type' => 'video',
            'activityId' => '1',
            'mediaSource' => 'self',
            'isFree' => '0',
            'isOptional' => '0',
            'startTime' => '0',
            'endTime' => '0',
            'length' => '300',
            'status' => 'create',
            'createdUserId' => '1',
        );
        if ($type == 'default') {
            $task = $this->getDefaultStrategy()->createTask($field);
        } else {
            $task = $this->getNormalStrategy()->createTask($field);
        }

        return $task;
    }

    protected function defaultCourse($title, $courseSet)
    {
        return  array(
            'title' => $title,
            'courseSetId' => $courseSet['id'],
            'expiryMode' => 'forever',
            'learnMode' => 'freeMode',
            'courseType' => 'normal',
        );
    }

    protected function createNewCourseSet()
    {
        $courseSetFields = array(
            'title' => '新课程开始！',
            'type' => 'normal',
        );
        $courseSet = $this->getCourseSetService()->createCourseSet($courseSetFields);

        $this->assertNotEmpty($courseSet);

        return $courseSet;
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    private function getDefaultStrategy()
    {
        return new DefaultStrategy($this->biz);
    }

    private function getNormalStrategy()
    {
        return new NormalStrategy($this->biz);
    }
}
