<?php

namespace Tests\Unit\Course\Service;

use Biz\BaseTestCase;
use AppBundle\Common\ReflectionUtils;

class PlayerServiceTest extends BaseTestCase
{
    public function testGetAudioAndVideoPlayerType()
    {
        $file = array('type' => 'audio', 'storage' => 'local');
        $result = $this->getPlayerService()->getAudioAndVideoPlayerType($file);
        $this->assertEquals('audio-player', $result);

        $file = array('type' => 'video', 'storage' => 'local');
        $result = $this->getPlayerService()->getAudioAndVideoPlayerType($file);
        $this->assertEquals('local-video-player', $result);

        $file = array('type' => 'video', 'storage' => 'cloud');
        $result = $this->getPlayerService()->getAudioAndVideoPlayerType($file);
        $this->assertEquals('balloon-cloud-video-player', $result);

        $file = array('type' => 'ppt', 'storage' => 'cloud');
        $result = $this->getPlayerService()->getAudioAndVideoPlayerType($file);
        $this->assertEquals(null, $result);
    }

    public function testAgentInWhiteList()
    {
        $result = $this->getPlayerService()->agentInWhiteList('iPhone');
        $this->assertTrue($result);
        $result = $this->getPlayerService()->agentInWhiteList('iPad');
        $this->assertTrue($result);
        $result = $this->getPlayerService()->agentInWhiteList('Android');
        $this->assertTrue($result);
        $result = $this->getPlayerService()->agentInWhiteList('HTC');
        $this->assertTrue($result);
        $result = $this->getPlayerService()->agentInWhiteList('HuaWei');
        $this->assertFalse($result);
    }

    public function testMakeToken($value = '')
    {
        $result = ReflectionUtils::invokeMethod($this->getPlayerService(),
            'makeToken',
            array(
                'hls.playlist',
                1,
                array('watchTimeLimit' => 5, 'hideBeginning' => true),
            )
        );
        $this->assertEquals('hls.playlist', $result['type']);
        $this->assertEquals(10, $result['times']);
        $this->assertArrayEquals(
            array('id' => 1, 'watchTimeLimit' => 5, 'hideBeginning' => true),
            $result['data']
        );
    }

    protected function getPlayerService()
    {
        return $this->createService('Player:PlayerService');
    }
}
