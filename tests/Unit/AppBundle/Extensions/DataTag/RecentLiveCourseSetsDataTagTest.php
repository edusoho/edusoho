<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\RecentLiveCourseSetsDataTag;

class RecentLiveCourseSetsDataTagTest extends BaseTestCase
{
    public function testGetDataEmpty()
    {
        $this->mockBiz('System:SettingService', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('live_course_enabled' => 0),
            ),
        ));
        $datatag = new RecentLiveCourseSetsDataTag();
        $data = $datatag->getData(array());

        $this->assertEmpty($data);
    }

    public function testGetData()
    {
        $this->mockBiz('System:SettingService', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('live_course_enabled' => 1),
            ),
        ));

        $courseSet1 = $this->getCourseSetService()->createCourseSet(array('type' => 'normal', 'title' => 'course set1 title'));
        $this->getCourseSetService()->publishCourseSet($courseSet1['id']);

        $courseSet2 = $this->getCourseSetService()->createCourseSet(array('type' => 'normal', 'title' => 'course set2 title'));
        $this->getCourseSetService()->publishCourseSet($courseSet2['id']);
        $courseSet3 = $this->getCourseSetService()->createCourseSet(array('type' => 'normal', 'title' => 'course set3 title'));

        $fields1 = array(
            'title' => 'task1 title',
            'courseId' => $courseSet1['defaultCourseId'],
            'fromCourseSetId' => $courseSet1['id'],
            'seq' => 1,
            'activityId' => 1,
            'type' => 'live',
            'startTime' => time() + 3600,
            'endTime' => time() + 3600 * 1,
            'mode' => '',
            'isFree' => 1,
            'createdUserId' => 1,
            'status' => 'published',
        );
        $task1 = $this->getTaskDao()->create($fields1);

        $fields2 = array(
            'title' => 'task1 title',
            'courseId' => $courseSet2['defaultCourseId'],
            'fromCourseSetId' => $courseSet2['id'],
            'seq' => 2,
            'activityId' => 2,
            'type' => 'live',
            'startTime' => time() + 3600 * 1,
            'endTime' => time() + 3600 * 2,
            'mode' => '',
            'isFree' => 1,
            'createdUserId' => 1,
            'status' => 'published',
        );
        $task2 = $this->getTaskDao()->create($fields2);

        $fields3 = array(
            'title' => 'task1 title',
            'courseId' => $courseSet3['defaultCourseId'],
            'fromCourseSetId' => $courseSet3['id'],
            'seq' => 3,
            'activityId' => 3,
            'type' => 'live',
            'startTime' => time() + 3600 * 2,
            'endTime' => time() + 3600 * 3,
            'mode' => '',
            'isFree' => 1,
            'createdUserId' => 1,
            'status' => 'published',
        );
        $task3 = $this->getTaskDao()->create($fields3);

        $fields4 = array(
            'title' => 'task1 title',
            'courseId' => $courseSet1['defaultCourseId'],
            'fromCourseSetId' => $courseSet1['id'],
            'seq' => 4,
            'activityId' => 4,
            'type' => 'live',
            'startTime' => time() + 3600 * 3,
            'endTime' => time() + 3600 * 4,
            'mode' => '',
            'isFree' => 1,
            'createdUserId' => 1,
            'status' => 'published',
        );
        $task4 = $this->getTaskDao()->create($fields4);

        $fields5 = array(
            'title' => 'task1 title',
            'courseId' => 11,
            'fromCourseSetId' => 10,
            'seq' => 5,
            'activityId' => 5,
            'type' => 'live',
            'startTime' => time() + 3600 * 4,
            'endTime' => time() + 3600 * 5,
            'mode' => '',
            'isFree' => 1,
            'createdUserId' => 1,
            'status' => 'published',
        );
        $task5 = $this->getTaskDao()->create($fields5);

        $datatag = new RecentLiveCourseSetsDataTag();
        $foundCourse = $datatag->getData(array('count' => 5));
        $this->assertEquals(3, count($foundCourse));

        $foundCourse = $datatag->getData(array('count' => 2));
        $this->assertEquals(2, count($foundCourse));
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
}
