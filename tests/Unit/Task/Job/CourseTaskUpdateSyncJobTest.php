<?php

namespace Tests\Unit\Task\Job;

use Biz\Task\Job\CourseTaskUpdateSyncJob;
use Biz\BaseTestCase;
use AppBundle\Common\ReflectionUtils;
use Tests\Unit\Task\Job\Tools\MockedText;

class CourseTaskUpdateSyncJobTest extends BaseTestCase
{
    public function testExecute()
    {
        $job = new CourseTaskUpdateSyncJob();
        ReflectionUtils::setProperty($job, 'biz', $this->biz);
        $job->args = array('taskId' => 110);

        $this->biz['activity_type.text'] = new MockedText($this->biz);
        $this->mockBiz(
            'Task:TaskService',
            array(
                array(
                    'functionName' => 'getTask',
                    'withParams' => array(110),
                    'returnValue' => array(
                        'id' => 110,
                        'courseId' => 3330,
                    ),
                ),
            )
        );
        $this->mockBiz(
            'Course:CourseDao',
            array(
                array(
                    'functionName' => 'findCoursesByParentIdAndLocked',
                    'withParams' => array(3330, 1),
                    'returnValue' => array(
                        array('id' => 3331),
                        array('id' => 3332),
                    ),
                ),
            )
        );
        $this->mockBiz(
            'Task:TaskDao',
            array(
                array(
                    'functionName' => 'findByCopyIdAndLockedCourseIds',
                    'withParams' => array(110, array(3331, 3332)),
                    'returnValue' => array(
                        array('id' => 20, 'activityId' => 44441, 'fromCourseSetId' => 55551, 'courseId' => 3331, 'mediaType' => 'text'),
                        array('id' => 21, 'activityId' => 44442, 'fromCourseSetId' => 55552, 'courseId' => 3332, 'mediaType' => 'text'),
                    ),
                ),
                array(
                    'functionName' => 'batchUpdate',
                    'withParams' => array(
                        array(20, 21),
                        array(
                            20 => array('id' => 20, 'activityId' => 44441, 'fromCourseSetId' => 55551, 'courseId' => 3331, 'mediaType' => 'text'),
                            21 => array('id' => 21, 'activityId' => 44442, 'fromCourseSetId' => 55552, 'courseId' => 3332, 'mediaType' => 'text'),
                        ),
                        'id',
                    ),
                    'returnValue' => array(
                        array('id' => 20, 'activityId' => 44441, 'fromCourseSetId' => 55551, 'courseId' => 3331, 'mediaType' => 'text'),
                        array('id' => 21, 'activityId' => 44442, 'fromCourseSetId' => 55552, 'courseId' => 3332, 'mediaType' => 'text'),
                    ),
                ),
            )
        );
        $this->mockBiz(
            'Activity:ActivityDao',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array(44441),
                    'returnValue' => array(
                        'id' => 44441,
                        'copyId' => 44451,
                        'mediaType' => 'text',
                    ),
                ),
                array(
                    'functionName' => 'get',
                    'withParams' => array(44442),
                    'returnValue' => array(
                        'id' => 44442,
                        'copyId' => 44452,
                        'mediaType' => 'text',
                    ),
                ),
                array(
                    'functionName' => 'get',
                    'withParams' => array(44451),
                    'returnValue' => array(
                        'id' => 44451,
                        'copyId' => 0,
                        'mediaType' => 'text',
                    ),
                ),
                array(
                    'functionName' => 'get',
                    'withParams' => array(44452),
                    'returnValue' => array(
                        'id' => 44452,
                        'copyId' => 0,
                        'mediaType' => 'text',
                    ),
                ),
                array(
                    'functionName' => 'update',
                    'withParams' => array(
                        44441,
                        array('id' => 44441, 'copyId' => 44451, 'mediaType' => 'text', 'mediaId' => 2222222),
                    ),
                ),
                array(
                    'functionName' => 'update',
                    'withParams' => array(
                        44442,
                        array('id' => 44442, 'copyId' => 44452, 'mediaType' => 'text', 'mediaId' => 2222222),
                    ),
                ),
            )
        );

        $job->execute();

        $mockedText = $this->biz['activity_type.text'];

        $this->assertArrayEquals(
            array('id' => 44452, 'copyId' => 0, 'mediaType' => 'text'),
            $mockedText->getSourceActivity()
        );

        $this->assertArrayEquals(
            array('id' => 44442, 'copyId' => 44452, 'mediaType' => 'text'),
            $mockedText->getActivity()
        );
    }

    /**
     * @return TaskService
     */
    private function getTaskService()
    {
        return $this->biz->dao('Task:TaskService');
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

    /**
     * @return ActivityDao
     */
    private function getActivityDao()
    {
        return $this->biz->dao('Activity:ActivityDao');
    }
}
