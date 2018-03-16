<?php

namespace Tests\Unit\Activity;

use Biz\BaseTestCase;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Biz\Activity\Service\ActivityService;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\ReflectionUtils;
use AppBundle\Common\TimeMachine;

class ActivityServiceTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->mockBiz(
            'Course:CourseService',
            array(
                array(
                    'functionName' => 'tryManageCourse',
                    'returnValue' => array('id' => 1),
                ),
            )
        );
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testCreateActivityWhenInvalidArgument()
    {
        $activity = array(
            'title' => 'test activity',
        );

        $savedActivity = $this->getActivityService()->createActivity($activity);
        $this->assertEquals($activity['title'], $savedActivity['title']);
    }

    // /**
    //  * @expectedException \AccessDeniedException
    //  */
    //
    // public function testCreateActivityWhenAccessDenied()
    // {
    //     $activity = array(
    //         'title' => 'test activity'
    //     );
    //     $savedActivity = $this->getActivityService()->createActivity($activity);
    //     $this->assertEquals($activity['title'], $savedActivity['title']);
    // }

    public function testCreateActivity()
    {
        $activity = array(
            'title' => 'test activity',
            'mediaType' => 'text',
            'fromCourseId' => 1,
            'fromCourseSetId' => 1,
        );
        $savedActivity = $this->getActivityService()->createActivity($activity);
        $this->assertEquals($activity['title'], $savedActivity['title']);
    }

    public function testUpdateActivity()
    {
        $activity = array(
            'title' => 'test activity',
            'mediaType' => 'text',
            'fromCourseId' => 1,
            'fromCourseSetId' => 1,
        );
        $savedActivity = $this->getActivityService()->createActivity($activity);

        $activity['title'] = 'course activity';
        $savedActivity = $this->getActivityService()->updateActivity($savedActivity['id'], $activity);

        $this->assertEquals($activity['title'], $savedActivity['title']);
    }

    public function testDeleteActivity()
    {
        $activity = array(
            'title' => 'test activity',
            'mediaType' => 'text',
            'fromCourseId' => 1,
            'fromCourseSetId' => 1,
        );
        $savedActivity = $this->getActivityService()->createActivity($activity);

        $this->assertNotNull($savedActivity);

        $this->mockBiz(
            'Course:CourseService',
            array(
                array(
                    'functionName' => 'tryManageCourse',
                    'returnValue' => 1,
                ),
                array(
                    'functionName' => 'updateCourseStatistics',
                    'returnValue' => 1,
                ),
            )
        );

        $this->getActivityService()->deleteActivity($savedActivity['id']);

        $savedActivity = $this->getActivityService()->getActivity($savedActivity['id']);
        $this->assertNull($savedActivity);
    }

    public function testTriggerStart()
    {
        $savedTask = $this->handleTriggerData();
        $data = array(
            'task' => $savedTask,
            'taskId' => $savedTask['id'],
        );
        $result = $this->getActivityService()->trigger(-1, 'start', $data);
        $this->assertEquals($result, false);
        $this->getActivityService()->trigger($savedTask['activityId'], 'start', $data);
        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($savedTask['id']);
        $activity = $this->getActivityLearnLogService()->getLastestLearnLogByActivityIdAndUserId($savedTask['activityId'], 1);
        $this->assertEquals($activity['event'], 'start');
        $this->assertEquals($activity['learnedTime'], 0);
    }

    public function testTriggerDoing()
    {
        $savedTask = $this->handleTriggerData();
        $this->mockBiz(
            'Task:TaskService',
            array(
                array(
                    'functionName' => 'doTask',
                    'returnValue' => 1,
                ),
                array(
                    'functionName' => 'isFinished',
                    'returnValue' => 0,
                ),
                array(
                    'functionName' => 'getTimeSec',
                    'returnValue' => 200,
                ),
            )
        );
        TimeMachine::setMockedTime(time());
        $data = array(
            'task' => $savedTask,
            'taskId' => $savedTask['id'],
            'lastTime' => TimeMachine::time() - 60,
            'events' => array(),
        );

        $this->getActivityService()->trigger($savedTask['activityId'], 'doing', $data);
        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($savedTask['id']);
        $activity = $this->getActivityLearnLogService()->getLastestLearnLogByActivityIdAndUserId($savedTask['activityId'], 1);
        $this->assertEquals($activity['event'], 'doing');
        $this->assertEquals($activity['learnedTime'], 60);
    }

    public function testTriggerWatching()
    {
        $savedTask = $this->handleTriggerData();
        $this->mockBiz(
            'Task:TaskService',
            array(
                array(
                    'functionName' => 'doTask',
                    'returnValue' => 1,
                ),
                array(
                    'functionName' => 'isFinished',
                    'returnValue' => 0,
                ),
                array(
                    'functionName' => 'getTimeSec',
                    'returnValue' => 200,
                ),
            )
        );
        TimeMachine::setMockedTime(time());
        $data = array(
            'task' => $savedTask,
            'taskId' => $savedTask['id'],
            'lastTime' => TimeMachine::time() - 60,
            'events' => array(
                'watching' => array('watchTime' => 120),
            ),
        );

        $this->getActivityService()->trigger($savedTask['activityId'], 'doing', $data);
        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($savedTask['id']);
        $learnLogs = $this->getActivityLearnLogService()->search(
            array('activityId' => $savedTask['activityId'], 'userId' => 1),
            array('createdTime' => 'DESC'),
            0,
            10
        );
        $learnLogs = ArrayToolKit::index($learnLogs, 'event');
        $this->assertArrayHasKey('doing', $learnLogs);
        $this->assertEquals($learnLogs['doing']['learnedTime'], 60);
        $this->assertArrayHasKey('watching', $learnLogs);
        $this->assertEquals($learnLogs['watching']['learnedTime'], 120);
    }

    public function testTriggerFinish()
    {
        $savedTask = $this->handleTriggerData();
        TimeMachine::setMockedTime(time());
        $data = array(
            'task' => $savedTask,
            'taskId' => $savedTask['id'],
            'lastTime' => TimeMachine::time() - 60,
            'events' => array(
                'finish' => array('data' => array('stop' => true)),
            ),
        );

        $this->getActivityService()->trigger($savedTask['activityId'], 'doing', $data);
        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($savedTask['id']);
        $activity = $this->getActivityLearnLogService()->getLastestLearnLogByActivityIdAndUserId($savedTask['activityId'], 1);
        $this->assertEquals($activity['event'], 'finish');
        $this->assertEquals($activity['learnedTime'], 0);
    }

    protected function handleTriggerData()
    {
        $course = array(
            'id' => 1,
            'title' => 'test',
            'courseSetId' => 1,
            'expiryMode' => 'forever',
            'learnMode' => 'lockMode',
            'isDefault' => 0,
            'status' => 'published',
            'parentId' => 0,
            'type' => 'normal',
            'rating' => 0,
            'summary' => '',
            'price' => 0,
            'courseType' => 'normal',
        );

        $this->mockBiz(
            'Course:CourseService',
            array(
                array(
                    'functionName' => 'tryManageCourse',
                    'returnValue' => 1,
                ),
                array(
                    'functionName' => 'tryTakeCourse',
                    'returnValue' => 1,
                ),
                array(
                    'functionName' => 'canTakeCourse',
                    'returnValue' => 1,
                ),
                array(
                    'functionName' => 'getCourse',
                    'returnValue' => $course,
                ),
                array(
                    'functionName' => 'updateCourseStatistics',
                    'returnValue' => 1,
                ),
                array(
                    'functionName' => 'getChapter',
                    'returnValue' => array('id' => 1, 'type' => 'lesson', 'status' => 'create'),
                ),
            )
        );

        $task = array(
            'title' => 'test1 task',
            'mediaType' => 'text',
            'fromCourseId' => $course['id'],
            'fromCourseSetId' => 1,
            'categoryId' => 1,
        );
        $savedTask = $this->getTaskService()->createTask($task);

        return $savedTask;
    }

    public function testSearch()
    {
        $activity1 = array(
            'title' => 'test activity',
            'mediaType' => 'text',
            'fromCourseId' => 1,
            'fromCourseSetId' => 1,
        );
        $savedActivity1 = $this->getActivityService()->createActivity($activity1);

        $activity2 = array(
            'title' => 'test activity2',
            'mediaType' => 'text',
            'fromCourseId' => 2,
            'fromCourseSetId' => 1,
        );
        $savedActivity2 = $this->getActivityService()->createActivity($activity2);

        $conditions = array(
            'fromCourseId' => 1,
            'mediaType' => 'text',
        );
        $activities = $this->getActivityService()->search($conditions, null, 0, 10);

        $this->assertEquals(1, count($activities));
        $this->assertArrayEquals($savedActivity1, $activities[0]);
    }

    public function testCount()
    {
        $activity1 = array(
            'title' => 'test activity',
            'mediaType' => 'text',
            'fromCourseId' => 1,
            'fromCourseSetId' => 1,
        );
        $savedActivity1 = $this->getActivityService()->createActivity($activity1);

        $activity2 = array(
            'title' => 'test activity2',
            'mediaType' => 'text',
            'fromCourseId' => 2,
            'fromCourseSetId' => 1,
        );
        $savedActivity2 = $this->getActivityService()->createActivity($activity2);

        $conditions = array(
            'fromCourseId' => 1,
            'mediaType' => 'text',
        );
        $count = $this->getActivityService()->count($conditions);

        $this->assertEquals(1, $count);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     * @expectedExceptionMessage activity.missing_params
     */
    public function testPreCreateCheckWithMissingParams()
    {
        $this->getActivityService()->preCreateCheck('live', array());
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     * @expectedExceptionMessage activity.live.overlap_time
     */
    public function testPreCreateCheckWithOverlapTime()
    {
        $this->mockBiz('Activity:ActivityDao', array(
            array('functionName' => 'findOverlapTimeActivitiesByCourseId', 'returnValue' => 1),
        ));
        $this->getActivityService()->preCreateCheck('live', array('fromCourseId' => 1, 'startTime' => 2, 'length' => 3));
    }

    public function testPreCreateCheck()
    {
        $this->getActivityService()->preCreateCheck('live', array('fromCourseId' => 1, 'startTime' => 2, 'length' => 3));
    }

    public function testPreUpdateCheck()
    {
        $this->mockBiz('Activity:ActivityDao', array(
           array('functionName' => 'get', 'returnValue' => array('id' => 1, 'mediaType' => 'live')),
            array('functionName' => 'findOverlapTimeActivitiesByCourseId', 'returnValue' => null),
        ));

        $this->getActivityService()->preUpdateCheck(1, array('fromCourseId' => 1, 'startTime' => 2, 'length' => 3));
    }

    public function testGetActivity()
    {
        $this->mockBiz(
            'Activity:ActivityDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 111, 'title' => 'title'),
                    'withParams' => array(1),
                ),
            )
        );

        $activity = $this->getActivityService()->getActivity(1);

        $this->assertEquals(array('id' => 111, 'title' => 'title'), $activity);
    }

    public function testGetActivityByCopyIdAndCourseSetId()
    {
        $this->mockBiz(
            'Activity:ActivityDao',
            array(
                array(
                    'functionName' => 'getByCopyIdAndCourseSetId',
                    'returnValue' => array('id' => 111, 'title' => 'title'),
                    'withParams' => array(1, 1),
                ),
            )
        );

        $activity = $this->getActivityService()->getActivityByCopyIdAndCourseSetId(1, 1);

        $this->assertEquals(array('id' => 111, 'title' => 'title'), $activity);
    }

    public function testFindActivities()
    {
        $this->mockBiz(
            'Activity:ActivityDao',
            array(
                array(
                    'functionName' => 'findByIds',
                    'returnValue' => array(array(
                        'id' => 111,
                        'title' => 'title',
                        'mediaType' => 'text',
                        'fromCourseId' => 1,
                        'fromCourseSetId' => 1,
                        'mediaId' => 1,
                    )),
                    'withParams' => array(array(1, 2)),
                ),
            )
        );

        $activities = $this->getActivityService()->findActivities(array(1, 2));

        $this->assertEquals(
            array(
                'id' => 111,
                'title' => 'title',
                'mediaType' => 'text',
                'fromCourseId' => 1,
                'fromCourseSetId' => 1,
                'mediaId' => 1,
            ),
            $activities[0]
        );

        $activities = $this->getActivityService()->findActivities(array(1, 2), true);

        $this->assertArrayHasKey('ext', $activities[0]);
    }

    public function testFindActivitiesByCourseIdAndType()
    {
        $this->mockBiz(
            'Activity:ActivityDao',
            array(
                array(
                    'functionName' => 'search',
                    'returnValue' => array(array(
                        'id' => 111,
                        'title' => 'title',
                        'mediaType' => 'text',
                        'fromCourseId' => 1,
                        'fromCourseSetId' => 1,
                        'mediaId' => 1,
                    )),
                    'withParams' => array(
                        array('fromCourseId' => 1, 'mediaType' => 'text'),
                        null,
                        0,
                        1000,
                    ),
                ),
            )
        );

        $activities = $this->getActivityService()->findActivitiesByCourseIdAndType(1, 'text', true);

        $this->assertEquals(111, $activities[0]['id']);
        $this->assertArrayHasKey('ext', $activities[0]);
    }

    public function testFindActivitiesByCourseSetIdAndType()
    {
        $this->mockBiz(
            'Activity:ActivityDao',
            array(
                array(
                    'functionName' => 'search',
                    'returnValue' => array(array(
                        'id' => 111,
                        'title' => 'title',
                        'mediaType' => 'text',
                        'fromCourseId' => 1,
                        'fromCourseSetId' => 1,
                        'mediaId' => 1,
                    )),
                    'withParams' => array(
                        array('fromCourseSetId' => 1, 'mediaType' => 'text'),
                        null,
                        0,
                        1000,
                    ),
                ),
            )
        );

        $activities = $this->getActivityService()->findActivitiesByCourseSetIdAndType(1, 'text', true);

        $this->assertEquals(111, $activities[0]['id']);
        $this->assertArrayHasKey('ext', $activities[0]);
    }

    public function testIsFinished()
    {
        $activity1 = array(
            'title' => 'test activity',
            'mediaType' => 'text',
            'fromCourseId' => 1,
            'fromCourseSetId' => 1,
        );
        $savedActivity1 = $this->getActivityService()->createActivity($activity1);

        $result = $this->getActivityService()->isFinished(1);

        $this->assertTrue($result);
    }

    public function testFindActivitySupportVideoTryLook()
    {
        $this->mockBiz(
            'Activity:ActivityDao',
            array(
                array(
                    'functionName' => 'findSelfVideoActivityByCourseIds',
                    'returnValue' => array(array(
                        'id' => 111,
                        'title' => 'title',
                        'mediaType' => 'text',
                        'fromCourseId' => 1,
                        'fromCourseSetId' => 1,
                        'fileId' => 1,
                    )),
                    'withParams' => array(array(1, 2)),
                ),
            )
        );

        $result = $this->getActivityService()->findActivitySupportVideoTryLook(array(1, 2));

        $this->assertEquals(array(), $result);
    }

    public function testGetMaterialsFromActivity()
    {
        $result1 = $this->getActivityService()->getMaterialsFromActivity(
            array('materials' => '{"id" : 1}'),
            'text'
        );

        $this->assertEquals(1, $result1['id']);

        $result2 = $this->getActivityService()->getMaterialsFromActivity(
            array('media' => '{"id" : 1}'),
            'text'
        );

        $this->assertEquals(1, $result2[0]['id']);
    }

    public function testFetchMedia()
    {
        $result1 = $this->getActivityService()->fetchMedia(array());

        $this->assertEquals(array(), $result1);

        $result2 = $this->getActivityService()->fetchMedia(array('mediaId' => 1, 'mediaType' => 'text'));

        $this->assertNull($result2['ext']);
    }

    public function testFetchMedias()
    {
        $results = $this->getActivityService()->fetchMedias('text', array(array('mediaId' => 1)));

        $this->assertArrayHasKey('ext', $results[0]);
    }

    public function testBuildMaterial()
    {
        $material = array(
            'id' => 1,
            'name' => 'material',
            'summary' => 'summary',
            'link' => 'www.edusoho.com',
        );
        $activity = array(
            'fromCourseId' => 2,
            'fromCourseSetId' => 3,
            'id' => 4,
            'mediaType' => 'video',
        );
        $result = ReflectionUtils::invokeMethod($this->getActivityService(), 'buildMaterial', array($material, $activity));
        $this->assertEquals($result['fileId'], 1);
        $this->assertEquals($result['courseId'], 2);
        $this->assertEquals($result['courseSetId'], 3);
        $this->assertEquals($result['lessonId'], 4);
        $this->assertEquals($result['title'], 'material');
        $this->assertEquals($result['description'], 'summary');
        $this->assertEquals($result['source'], 'courseactivity');
        $this->assertEquals($result['link'], 'www.edusoho.com');
    }

    public function testDiffMaterials()
    {
        $arr1 = array(
            array(
                'fileId' => 0,
                'link' => 'www.edusoho.com0',
            ),
            array(
                'fileId' => 1,
                'link' => 'www.edusoho.com1',
            ),
        );
        $arr2 = array();
        $result = ReflectionUtils::invokeMethod($this->getActivityService(), 'diffMaterials', array($arr1, $arr2));
        $this->assertEquals($result[0]['link'], 'www.edusoho.com0');

        $arr2 = array(
            array(
                'fileId' => 0,
                'link' => 'www.edusoho.com0',
            ),
            array(
                'fileId' => 2,
                'link' => 'www.edusoho.com1',
            ),
        );
        $result = ReflectionUtils::invokeMethod($this->getActivityService(), 'diffMaterials', array($arr1, $arr2));
        $this->assertEquals($result[0]['fileId'], 1);
        $this->assertEquals($result[0]['link'], 'www.edusoho.com1');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getActivityLearnLogService()
    {
        return $this->createService('Activity:ActivityLearnLogService');
    }
}
