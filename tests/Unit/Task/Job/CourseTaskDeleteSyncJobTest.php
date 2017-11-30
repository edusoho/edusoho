<?php

namespace Tests\Unit\Task\Job;

use Biz\Task\Job\CourseTaskDeleteSyncJob;
use Biz\BaseTestCase;
use AppBundle\Common\ReflectionUtils;
use Tests\Unit\Task\Job\Tools\MockedNormalStrategy;

class CourseTaskDeleteSyncJobTest extends BaseTestCase
{
    public function testExecute()
    {
        $job = new CourseTaskDeleteSyncJob();
        ReflectionUtils::setProperty($job, 'biz', $this->biz);
        $job->args = array('taskId' => 110, 'courseId' => 220);

        $this->biz['course.normal_strategy'] = new MockedNormalStrategy();

        $this->mockBiz(
            'Course:CourseDao',
            array(
                array(
                    'functionName' => 'findCoursesByParentIdAndLocked',
                    'withParams' => array(220, 1),
                    'returnValue' => array(
                        array('id' => 3331, 'courseSetId' => 222, 'courseType' => 'normal'),
                        array('id' => 3332, 'courseType' => 'normal'),
                    ),
                ),
            )
        );

        $this->mockBiz(
            'Task:TaskDao',
            array(
                array(
                    'functionName' => 'findByCopyIdAndLockedCourseIds',
                    'withParams' => array(
                        110,
                        array(3331, 3332),
                    ),
                    'returnValue' => array(
                        array('id' => 231, 'courseId' => 3331),
                        array('id' => 232, 'courseId' => 3332),
                    ),
                ),
                array(
                    'functionName' => 'get',
                    'withParams' => array(231),
                    'returnValue' => array(
                        'id' => 231,
                        'courseId' => 3331,
                    ),
                ),
                array(
                    'functionName' => 'get',
                    'withParams' => array(232),
                    'returnValue' => array(
                        'id' => 232,
                        'courseId' => 3332,
                    ),
                ),
            )
        );

        $job->execute();

        $this->getTaskDao()->shouldHaveReceived('findByCopyIdAndLockedCourseIds')->times(1);
        $this->getTaskDao()->shouldHaveReceived('get')->times(2);
        $this->getCourseDao()->shouldHaveReceived('findCoursesByParentIdAndLocked')->times(1);

        $strategy = $this->biz['course.normal_strategy'];

        $this->assertEquals(
            array(
                array('id' => 231, 'courseId' => 3331),
                array('id' => 232, 'courseId' => 3332),
            ),
            $strategy->getDeletedTasks()
        );
    }

    /**
     * @return TaskDao
     */
    private function getTaskDao()
    {
        return $this->biz->dao('Task:TaskDao');
    }

    /**
     * @return CourseDao
     */
    private function getCourseDao()
    {
        return $this->biz->dao('Course:CourseDao');
    }
}
