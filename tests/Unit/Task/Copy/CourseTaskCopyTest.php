<?php

namespace Tests\Unit\AppBundle\Component\RateLimit;

use Biz\BaseTestCase;
use Biz\Task\Copy\CourseTaskCopy;

class CourseTaskCopyTest extends BaseTestCase
{
    public function testDoCopy()
    {
        $oldCourseId = 1122;
        $newCourseId = 11223;
        $taskDao = $this->mockBiz(
            'Task:TaskDao',
            array(
                array(
                    'functionName' => 'findByCourseId',
                    'withParams' => array($oldCourseId),
                    'returnValue' => array(
                        array(
                            'id' => 11221,
                            'seq' => 1,
                            'activityId' => 2222,
                            'categoryId' => 54,
                            'title' => 'task title',
                            'isFree' => 0,
                            'isOptional' => 0,
                            'startTime' => 1123321421,
                            'endTime' => 1123351421,
                            'mode' => 'no',
                            'number' => 1,
                            'type' => 'video',
                            'mediaSource' => 'dses',
                            'status' => 'ok',
                            'length' => 12312,
                        ),
                    ),
                ),
                array(
                    'functionName' => 'batchCreate',
                    'withParams' => array(
                        array(
                            array(
                                'seq' => 1,
                                'activityId' => 2222,
                                'categoryId' => 54,
                                'title' => 'task title',
                                'isFree' => 0,
                                'isOptional' => 0,
                                'startTime' => 1123321421,
                                'endTime' => 1123351421,
                                'mode' => 'no',
                                'number' => 1,
                                'type' => 'video',
                                'mediaSource' => 'dses',
                                'status' => 'ok',
                                'length' => 12312,
                                'courseId' => $newCourseId,
                                'fromCourseSetId' => 123,
                                'createdUserId' => 1,
                                'copyId' => 11221,
                            ),
                        ),
                    ),
                ),
            )
        );

        $courseChaperDao = $this->mockBiz(
            'Course:CourseChapterDao',
            array(
                array(
                    'functionName' => 'findChaptersByCourseId',
                    'withParams' => array($newCourseId),
                    'returnValue' => array(
                        array(
                            'copyId' => 101,
                        ),
                    ),
                ),
            )
        );

        $activityDao = $this->mockBiz(
            'Activity:ActivityDao',
            array(
                array(
                    'functionName' => 'findByCourseId',
                    'withParams' => array($newCourseId),
                    'returnValue' => array(
                        array(
                            'copyId' => 201,
                        ),
                    ),
                ),
            )
        );
        $copy = new CourseTaskCopy($this->biz, null);

        $result = $copy->doCopy(
            array(),
            array(
                'originCourse' => array(
                    'id' => $oldCourseId,
                ),
                'newCourse' => array(
                    'id' => $newCourseId,
                ),
                'newCourseSet' => array(
                    'id' => 123,
                ),
            )
        );

        $this->assertNull($result);
        $taskDao->shouldHaveReceived('findByCourseId')->times(1);
        $taskDao->shouldHaveReceived('batchCreate')->times(1);
        $courseChaperDao->shouldHaveReceived('findChaptersByCourseId')->times(1);
        $activityDao->shouldHaveReceived('findByCourseId')->times(1);
    }
}
