<?php

namespace Tests\Unit\Activity\Service;

use Biz\BaseTestCase;
use Biz\Activity\Service\LiveActivityService;
use AppBundle\Common\ReflectionUtils;

class LiveActivityServiceTest extends BaseTestCase
{
    public function testCreate()
    {
        $live = array(
            'title' => 'test live activity',
            'remark' => 'remark ...',
            'mediaType' => 'live',
            'fromCourseId' => 1,
            'fromCourseSetId' => 1,
            'fromUserId' => '1',
            'startTime' => time() + 1000,
            'endTime' => time() + 3000,
            'length' => 2000,
            '_base_url' => 'url...',
            'roomType' => 'small',
        );
        $savedActivity = $this->getLiveActivityService()->createLiveActivity($live);
        $this->assertNotNull($savedActivity['id']);
        $this->assertNotNull($savedActivity['liveId']);
        $this->assertNotNull($savedActivity['liveProvider']);
        $this->assertEquals('small', $savedActivity['roomType']);
    }

    public function testUpdate()
    {
        $live = array(
            'title' => 'test live activity 2',
            'remark' => 'remark ...',
            'mediaType' => 'live',
            'fromCourseId' => 1,
            'fromCourseSetId' => 1,
            'fromUserId' => '1',
            'startTime' => time() + 1000,
            'endTime' => time() + 4000,
            'length' => 3,
            'roomType' => 'small',
        );
        $savedActivity = $this->getLiveActivityService()->createLiveActivity($live);
        $savedActivity = array_merge($savedActivity, $live);
        $savedActivity['startTime'] = time() + 2000;
        $savedActivity['endTime'] = time() + 5000;
        $updatedData = array('length' => 100, 'endTime' => time() + 100000);
        $updatedActivity = $this->getLiveActivityService()->updateLiveActivity($savedActivity['id'], $updatedData, $savedActivity);
        $this->assertEquals($savedActivity['liveId'], $updatedActivity['liveId']);
    }

    public function testDelete()
    {
        $live = array(
            'title' => 'test live activity 2',
            'remark' => 'remark ...',
            'mediaType' => 'live',
            'fromCourseId' => 1,
            'fromCourseSetId' => 1,
            'fromUserId' => '1',
            'startTime' => time() + 1000,
            'endTime' => time() + 4000,
            'length' => 3000,
            '_base_url' => 'url...',
            'roomType' => 'large',
        );
        $savedActivity = $this->getLiveActivityService()->createLiveActivity($live);
        $this->getLiveActivityService()->deleteLiveActivity($savedActivity['id']);
        $result = $this->getLiveActivityService()->getLiveActivity($savedActivity['id']);
        $this->assertNull($result);
    }

    public function testGetLiveActivity()
    {
        $this->mockBiz(
            'Activity:LiveActivityDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 111, 'liveId' => 111),
                    'withParams' => array(1),
                ),
            )
        );
        $result = $this->getLiveActivityService()->getLiveActivity(1);

        $this->assertEquals(array('id' => 111, 'liveId' => 111), $result);
    }

    public function testFindLiveActivitiesByIds()
    {
        $this->mockBiz(
            'Activity:LiveActivityDao',
            array(
                array(
                    'functionName' => 'findByIds',
                    'returnValue' => array(array('id' => 111, 'liveId' => 111)),
                    'withParams' => array(array(1, 2)),
                ),
            )
        );

        $results = $this->getLiveActivityService()->findLiveActivitiesByIds(array(1, 2));

        $this->assertEquals(array(array('id' => 111, 'liveId' => 111)), $results);
    }

    public function testCreateLiveroom()
    {
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'getUser',
                    'returnValue' => array('id' => 111, 'nickname' => 'test'),
                    'withParams' => array(111),
                ),
            )
        );
        $liveRoom = $this->getLiveActivityService()->createLiveroom(array(
            'fromUserId' => 111,
            'remark' => 'test',
            'startTime' => 4541222,
            'length' => 2,
            'title' => 'test',
            'fromCourseId' => 12,
            'roomType' => 'large',
        ));
    }

    public function testCanUpdateRoomType()
    {
        $liveTime = time() + 3600 * 2 + 10;
        $result = $this->getLiveActivityService()->canUpdateRoomType($liveTime);
        $this->assertEquals(1, $result);

        $liveTime = time() + 3600 * 2 - 10;
        $result = $this->getLiveActivityService()->canUpdateRoomType($liveTime);
        $this->assertEquals(0, $result);

        $liveTime = time() - 5 * 60;
        $result = $this->getLiveActivityService()->canUpdateRoomType($liveTime);
        $this->assertEquals(0, $result);
    }

    public function testIsRoomType()
    {
        $result = ReflectionUtils::invokeMethod($this->getLiveActivityService(), 'isRoomType', array('small'));
        $this->assertTrue($result);

        $result = ReflectionUtils::invokeMethod($this->getLiveActivityService(), 'isRoomType', array('large'));
        $this->assertTrue($result);

        $result = ReflectionUtils::invokeMethod($this->getLiveActivityService(), 'isRoomType', array('middle'));
        $this->assertFalse($result);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     * @expectedExceptionMessage Argument invalid
     */
    public function testUpdateLiveStatusActivityEmpty()
    {
        $result = $this->getLiveActivityService()->updateLiveStatus(1, 'closed');
        $this->assertNull($result);

        $this->mockBiz('Activity:LiveActivityDao', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('id' => 1),
            ),
        ));
        $result = $this->getLiveActivityService()->updateLiveStatus(1, 'created');
    }

    public function testUpdateLiveStatus()
    {
        $this->mockBiz('Activity:LiveActivityDao', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('id' => 1, 'progressStatus' => 'created'),
            ),
            array(
                'functionName' => 'update',
                'returnValue' => array('id' => 1, 'progressStatus' => 'closed'),
            ),
        ));
        $result = $this->getLiveActivityService()->updateLiveStatus(1, 'closed');

        $this->assertEquals('closed', $result['progressStatus']);
    }

    public function testSearch()
    {
        $this->mockBiz('Activity:LiveActivityDao', array(
            array(
                'functionName' => 'search',
                'returnValue' => array(array('id' => 1), array('id' => 2)),
            ),
        ));

        $results = $this->getLiveActivityService()->search(array('ids' => array(1, 2, 3)), null, 0, 5);

        $this->assertEquals(2, count($results));
    }

    /**
     * @return LiveActivityService
     */
    protected function getLiveActivityService()
    {
        $service = $this->createService('Activity:LiveActivityService');
        //mock client
        ReflectionUtils::setProperty($service, 'client', new MockEdusohoLiveClient());

        return $service;
    }
}

/*
Mock of Topxia\Service\Util\EdusohoLiveClient
 */
class MockEdusohoLiveClient
{
    public function __contruct()
    {
    }

    public function createLive($live)
    {
        return array(
            'id' => rand(1, 1000),
            'provider' => rand(1, 10),
        );
    }

    public function updateLive($live)
    {
        return $live;
    }

    public function deleteLive($id)
    {
        return array(
            'id' => $id,
        );
    }
}
