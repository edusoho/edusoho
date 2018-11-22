<?php

namespace Tests\Unit\Live\Service;

use Biz\BaseTestCase;
use Biz\Util\EdusohoLiveClient;
use Mockery;
use AppBundle\Common\ReflectionUtils;

class LiveServiceTest extends BaseTestCase
{
    public function testCreateLiveRoom()
    {
        $mockFields = array('id' => 123, 'provider' => 6);
        $this->mockLiveClient('createLive', $mockFields);

        $user = $this->getCurrentUser();
        $result = $this->getLiveService()->createLiveRoom(array('startTime' => time() + 3600, 'endTime' => time() + 7200, 'speakerId' => $user['id'], 'type' => 'course'));

        $this->assertArrayEquals($mockFields, $result);
    }

    /**
     * @expectedException \Exception
     */
    public function testCreateLiveRoomThrowException()
    {
        $this->mockLiveClientThrowError('createLive', new \Exception('Invalid Argument'));

        $user = $this->getCurrentUser();
        $this->getLiveService()->createLiveRoom(array('startTime' => time() + 3600, 'endTime' => time() + 7200, 'speakerId' => $user['id'], 'type' => 'course'));
    }

    public function testUpdateLiveRoom()
    {
        $liveId = 1;
        $mockFields = array(
            'id' => $liveId,
            'title' => 'live title',
        );
        $this->mockLiveClient('updateLive', $mockFields);

        $result = $this->getLiveService()->updateLiveRoom($liveId, array('title' => 'live title'));
        $this->assertArrayEquals($mockFields, $result);
    }

    /**
     * @expectedException \Exception
     */
    public function testUpdateLiveRoomThrowException()
    {
        $this->mockLiveClientThrowError('updateLive', new \Exception('Invalid Argument'));

        $this->getLiveService()->updateLiveRoom(1, array('title' => 'live title'));
    }

    public function testDeleteLiveRoomEmptyLiveId()
    {
        $result = $this->getLiveService()->deleteLiveRoom(0);

        $this->assertEmpty($result);
    }

    public function testDeleteLiveRoom()
    {
        $this->mockLiveClient('deleteLive', array('success' => true));

        $liveId = 1;
        $result = $this->getLiveService()->deleteLiveRoom($liveId);

        $this->assertTrue($result['success']);
    }

    /**
     * @expectedException \Exception
     */
    public function testDeleteLiveRoomThrowException()
    {
        $this->mockLiveClientThrowError('deleteLive', new \Exception());
        $this->getLiveService()->deleteLiveRoom(1);
    }

    public function testCanUpdateRoomType()
    {
        $liveTime = time() + 3600 * 2 + 10;
        $result = $this->getLiveService()->canUpdateRoomType($liveTime);
        $this->assertEquals(1, $result);

        $liveTime = time() + 3600 * 2 - 10;
        $result = $this->getLiveService()->canUpdateRoomType($liveTime);
        $this->assertEquals(0, $result);

        $liveTime = time() - 5 * 60;
        $result = $this->getLiveService()->canUpdateRoomType($liveTime);
        $this->assertEquals(0, $result);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testFilterCreateParamsInvalidArguments()
    {
        $args = array(array(
            'startTime' => time() + 3000,
        ));
        ReflectionUtils::invokeMethod($this->getLiveService(), 'filterCreateParams', $args);
    }

    public function testFilterCreateParams()
    {
        $user = $this->getCurrentUser();
        $params = array(
            'startTime' => time() + 3000,
            'endTime' => time() + 5000,
            'speakerId' => $user['id'],
            'type' => 'live',
        );
        $result = ReflectionUtils::invokeMethod($this->getLiveService(), 'filterCreateParams', array($params));

        $this->assertArrayHasKey('liveLogoUrl', $result);
        $this->assertArrayHasKey('speaker', $result);
        $this->assertArrayHasKey('authUrl', $result);
        $this->assertArrayHasKey('jumpUrl', $result);
    }

    public function testFilterCreateParamsHasCallback()
    {
        $user = $this->getCurrentUser();
        $params = array(
            'startTime' => time() + 3000,
            'endTime' => time() + 5000,
            'speakerId' => $user['id'],
            'type' => 'live',
            'isCallback' => 1,
            'targetId' => 1,
            'targetType' => 'course',
        );
        $result = ReflectionUtils::invokeMethod($this->getLiveService(), 'filterCreateParams', array($params));

        $this->assertArrayHasKey('callback', $result);
    }

    public function testFilterCreateParamsHasRoomType()
    {
        $user = $this->getCurrentUser();
        $params = array(
            'startTime' => time() + 3000,
            'endTime' => time() + 5000,
            'speakerId' => $user['id'],
            'type' => 'live',
            'roomType' => 'large',
        );
        $result = ReflectionUtils::invokeMethod($this->getLiveService(), 'filterCreateParams', array($params));

        $this->assertArrayHasKey('roomType', $result);
    }

    public function testFilterUpdateParams()
    {
        $params = array(
            'liveId' => 1,
            'title' => 'live room title',
        );
        $result = ReflectionUtils::invokeMethod($this->getLiveService(), 'filterUpdateParams', array($params));

        $this->assertArrayEquals($params, $result);
    }

    public function testFilterUpdateParamsHasStartTime()
    {
        $params = array(
            'liveId' => 1,
            'title' => 'live room title',
            'startTime' => time() + 3600,
            'endTime' => time() + 5000,
        );
        $result = ReflectionUtils::invokeMethod($this->getLiveService(), 'filterUpdateParams', array($params));

        $this->assertArrayHasKey('startTime', $result);
        $this->assertArrayHasKey('endTime', $result);
    }

    public function testFilterUpdateParamsHasStartTimeAndRoomType()
    {
        $params = array(
            'liveId' => 1,
            'title' => 'live room title',
            'startTime' => time() + 3600 * 3,
            'endTime' => time() + 3600 * 4,
            'roomType' => 'large',
        );
        $result = ReflectionUtils::invokeMethod($this->getLiveService(), 'filterUpdateParams', array($params));

        $this->assertArrayHasKey('roomType', $result);
    }

    public function testGetLiveLogo()
    {
        $liveLogo = 'course.png';

        $this->mockBiz('System:SettingService', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('live_logo' => $liveLogo),
            ),
        ));

        $result = ReflectionUtils::invokeMethod($this->getLiveService(), 'getLiveLogo', array());

        $biz = $this->getBiz();
        $baseUrl = $biz['env']['base_url'];
        $this->assertEquals($baseUrl.'/'.$liveLogo, $result);
    }

    public function testBuildCallbackUrl()
    {
        $this->mockBiz('User:TokenService', array(
            array(
                'functionName' => 'makeToken',
                'returnValue' => array('token' => '123456'),
            ),
        ));
        $params = array(
            time() + 3600,
            1,
            'course',
            1,
        );
        $results = ReflectionUtils::invokeMethod($this->getLiveService(), 'buildCallbackUrl', $params);

        $this->assertEquals(1, count($results));
        $this->assertArrayHasKey('type', $results[0]);
        $this->assertArrayHasKey('url', $results[0]);
    }

    public function testGetSpeakerName()
    {
        $nickname = 'user nickname';
        $this->mockBiz('User:UserService', array(
            array(
                'functionName' => 'getUser',
                'returnValue' => array('id' => 1, 'nickname' => $nickname),
            ),
        ));
        $result = ReflectionUtils::invokeMethod($this->getLiveService(), 'getSpeakerName', array(1));

        $this->assertEquals($nickname, $result);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testGetSpeakerNameError()
    {
        ReflectionUtils::invokeMethod($this->getLiveService(), 'getSpeakerName', array(10));
    }

    private function mockLiveClient($functionName, $returnValues)
    {
        $liveClient = new EdusohoLiveClient();
        $mockObject = Mockery::mock($liveClient);
        $mockObject->shouldReceive($functionName)->times(1)->andReturn($returnValues);
        $biz = $this->getBiz();
        $biz['educloud.live_client'] = $mockObject;
    }

    private function mockLiveClientThrowError($functionName, $exception)
    {
        $liveClient = new EdusohoLiveClient();
        $mockObject = Mockery::mock($liveClient);
        $mockObject->shouldReceive($functionName)->andThrow($exception);
        $biz = $this->getBiz();
        $biz['educloud.live_client'] = $mockObject;
    }

    protected function getLiveService()
    {
        return $this->createService('Live:LiveService');
    }
}
