<?php

namespace Tests\Unit\User\Service;

use Biz\BaseTestCase;
use Biz\User\Dao\UserFootprintDao;
use Biz\User\Service\UserFootprintService;

class UserFootprintServiceTest extends BaseTestCase
{
    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_parameter_missing
     */
    public function testCreateUserFootprint_ParamMissingException()
    {
        $footprint = array(
            'userId' => 0,
            'targetType' => '',
        );

        $this->getFootprintService()->createUserFootprint($footprint);
    }

    public function testCreateUserFootprint()
    {
        $footprint = array(
            'id' => 1,
            'userId' => $this->getCurrentUser()->getId(),
            'targetType' => 'task',
            'targetId' => 1,
            'event' => 'learn',
            'date' => 1582010291,
        );

        $result = $this->getFootprintService()->createUserFootprint($footprint);

        $existedFootprint = $result;

        unset($result['createdTime']);
        unset($result['updatedTime']);

        $this->assertEquals($footprint, $result);

        sleep(1);
        $updatedFootprint = $this->getFootprintService()->createUserFootprint($footprint);

        $this->assertEquals($existedFootprint['date'], $updatedFootprint['date']);
        $this->assertNotEquals((int) $existedFootprint['updatedTime'], (int) $updatedFootprint['updatedTime']);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_parameter_missing
     */
    public function testUpdateUserFootprint_ParamMissingException()
    {
        $footprint = $this->createFootprint();

        unset($footprint['targetType']);
        $this->getFootprintService()->updateFootprint($footprint['id'], $footprint);
    }

    public function testUpdateUserFootprint()
    {
        $footprint = $this->createFootprint();

        sleep(1);
        $result = $this->getFootprintService()->updateFootprint($footprint['id'], $footprint);

        $this->assertNotEquals($footprint['updatedTime'], $result['updatedTime']);

        unset($footprint['updatedTime']);
        unset($result['updatedTime']);

        $this->assertEquals($footprint, $result);
    }

    public function testSearchUserFootprint()
    {
        $currentUserFootprint1 = $this->createFootprint();
        $currentUserFootprint2 = $this->createFootprint($this->getCurrentUser()->getId(), 'task', 2);
        $userFootprint1 = $this->createFootprint(100);
        $userFootprint2 = $this->createFootprint(100, 'task', 2, 'learn', time() + 24 * 60 * 60);

        $result = $this->getFootprintService()->searchUserFootprints(array(), array(), 0, 10);
        $this->assertEmpty($result);

        $result = $this->getFootprintService()->searchUserFootprints(array('userId' => $this->getCurrentUser()->getId()), array('updatedTime' => 'DESC'), 0, 10);

        $this->assertEquals(array($currentUserFootprint1, $currentUserFootprint2), $result);

        $result = $this->getFootprintService()->searchUserFootprints(array('userId' => 100), array('updatedTime' => 'DESC'), 0, 10);
        $this->assertEquals(array($userFootprint1, $userFootprint2), $result);

        $result = $this->getFootprintService()->searchUserFootprints(array('userId' => 100, 'date_GT' => time() + 24 * 24), array('updatedTime' => 'DESC'), 0, 10);
        $this->assertEquals(array($userFootprint2), $result);
    }

    public function testCountUserFootprint()
    {
        $result = $this->getFootprintService()->countUserFootprints(array());
        $this->assertEquals(0, $result);

        $this->createFootprint();
        $this->createFootprint($this->getCurrentUser()->getId(), 'task', 2);
        $this->createFootprint(100);

        $result = $this->getFootprintService()->countUserFootprints(array());
        $this->assertEquals(3, $result);

        $result = $this->getFootprintService()->countUserFootprints(array('userId' => $this->getCurrentUser()->getId()));
        $this->assertEquals(2, $result);
    }

    public function testPrepareUserFootprintsByType_MethodNotExist()
    {
        $footprints = array(
            array(
                'id' => 1,
                'userId' => 3,
                'targetType' => 'task',
                'targetId' => 1,
                'event' => 'learn',
                'date' => 1577938388,
                'createdTime' => 1577936308,
                'updatedTime' => 1577938388,
            ),
        );

        $result = $this->getFootprintService()->prepareUserFootprintsByType($footprints, 'testType');
        $this->assertEquals($footprints, $result);
    }

    public function testPrepareUserFootprintsByType_taskNotExist()
    {
        $footprints = array(
            'id' => 1,
            'userId' => 3,
            'targetType' => 'task',
            'targetId' => 1,
            'event' => 'learn',
            'date' => 1577938388,
            'createdTime' => 1577936308,
            'updatedTime' => 1577938388,
        );

        $result = $this->getFootprintService()->prepareUserFootprintsByType($footprints, 'task');

        $this->assertEmpty($result);
    }

    public function testPrepareTaskFootprintByType()
    {
        $footprints = array(
            array(
                'id' => 1,
                'userId' => 3,
                'targetType' => 'task',
                'targetId' => 1,
                'event' => 'learn',
                'date' => 1577938388,
                'createdTime' => 1577936308,
                'updatedTime' => 1577938388,
            ),
            array(
                'id' => 2,
                'userId' => 3,
                'targetType' => 'task',
                'targetId' => 3,
                'event' => 'learn',
                'date' => 1577938388,
                'createdTime' => 1577936308,
                'updatedTime' => 1577938388,
            ),
        );

        $this->mockBiz('Task:TaskService', array(
                array(
                    'functionName' => 'findTasksByIds',
                    'returnValue' => array(
                        array(
                            'id' => 1,
                            'courseId' => 1,
                            'seq' => 1,
                            'title' => 'test lesson title',
                            'isOptional' => '0',
                            'mode' => 'lesson',
                            'number' => '2',
                            'type' => 'text',
                            'isLesson' => 1,
                        ),
                        array(
                            'id' => 3,
                            'courseId' => 1,
                            'categoryId' => 2,
                            'seq' => 5,
                            'title' => 'test task title',
                            'isOptional' => '0',
                            'mode' => 'exercise',
                            'number' => '2-2',
                            'type' => 'text',
                            'isLesson' => 0,
                        ),
                    ),
                ),
                array(
                    'functionName' => 'searchTasks',
                    'returnValue' => array(
                        array(
                            'id' => 2,
                            'courseId' => 1,
                            'seq' => 4,
                            'categoryId' => 2,
                            'title' => 'test lesson task title',
                            'isOptional' => '0',
                            'mode' => 'lesson',
                            'number' => '2-1',
                            'type' => 'text',
                            'isLesson' => 1,
                        ),
                    ),
                ),
            )
        );

        $this->mockBiz('Course:CourseService', array(
                array(
                    'functionName' => 'findCoursesByIds',
                    'returnValue' => array(
                        array(
                            'id' => 1,
                            'title' => 'course title',
                        ),
                    ),
                ),
            )
        );

        $this->mockBiz('Classroom:ClassroomService', array(
            array(
                'functionName' => 'findClassroomsByCoursesIds',
                'returnValue' => array(
                    array(
                        'id' => 1,
                        'courseId' => 1,
                        'classroomId' => 1,
                    ),
                ),
            ),
            array(
                'functionName' => 'findClassroomsByIds',
                'returnValue' => array(
                    array(
                        'id' => 1,
                    ),
                ),
            ),
        ));
        $result = $this->getFootprintService()->prepareUserFootprintsByType($footprints, 'task');

        $this->assertEquals(2, count($result));

        $this->assertEquals('2', $result[0]['target']['number']);
        $this->assertEquals('test lesson title', $result[0]['target']['title']);

        $this->assertEquals('2-1', $result[1]['target']['number']);
        $this->assertEquals('test lesson task title', $result[1]['target']['title']);
    }

    public function testDeleteUserFootprintsByCreatedTime()
    {
        $footprint = array(
            'userId' => $this->getCurrentUser()->getId(),
            'targetType' => 'test type1',
            'targetId' => 1,
            'event' => 'test event',
            'date' => time(),
        );

        $this->getFootprintDao()->create($footprint);

        $footprint = array(
            'userId' => $this->getCurrentUser()->getId(),
            'targetType' => 'test type2',
            'targetId' => 1,
            'event' => 'test event',
            'date' => strtotime('-1 year', time()),
        );

        $this->getFootprintDao()->create($footprint);
        $footprint = array(
            'userId' => $this->getCurrentUser()->getId(),
            'targetType' => 'test type3',
            'targetId' => 1,
            'event' => 'test event',
            'date' => strtotime('-2 year', time()) - 60 * 60,
        );

        $this->getFootprintDao()->create($footprint);
        $footprint = array(
            'userId' => $this->getCurrentUser()->getId(),
            'targetType' => 'test type4',
            'targetId' => 1,
            'event' => 'test event',
            'date' => strtotime('-2 year', time()),
        );

        $this->getFootprintDao()->create($footprint);

        $count = $this->getFootprintService()->countUserFootprints(array());
        $this->assertEquals(4, $count);

        $this->getFootprintService()->deleteUserFootprintsBeforeDate(strtotime('-2 year', time()));

        $count = $this->getFootprintService()->countUserFootprints(array());

        $this->assertEquals(2, $count);
    }

    protected function createFootprint($userId = null, $targetType = 'task', $targetId = 1, $event = 'learn', $date = 0)
    {
        $footprint = array(
            'userId' => empty($userId) ? $this->getCurrentUser()->getId() : $userId,
            'targetType' => $targetType,
            'targetId' => $targetId,
            'event' => $event,
            'date' => empty($date) ? time() : $date,
        );

        return $this->getFootprintService()->createUserFootprint($footprint);
    }

    /**
     * @return UserFootprintService
     */
    protected function getFootprintService()
    {
        return $this->createService('User:UserFootprintService');
    }

    /**
     * @return UserFootprintDao
     */
    protected function getFootprintDao()
    {
        return $this->createDao('User:UserFootprintDao');
    }
}
