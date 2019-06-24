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

    public function testGetVideoFilePlayer()
    {
        $this->mockBiz('System:SettingService', array(
            array('functionName' => 'get', 'returnValue' => array('enable_hls_encryption_plus' => true, 'video_header' => true, 'support_mobile' => 1)),
        ));
        $this->mockBiz('File:UploadFileService', array(
            array('functionName' => 'getFileByTargetType', 'returnValue' => array('convertStatus' => 'success', 'length' => 10)),
        ));
        $this->mockBiz('MaterialLib:MaterialLibService', array(
            array('functionName' => 'player', 'returnValue' => array('subtitles' => array(array('name' => 'test.srt')), 'mp4url' => 'www.baidu.com')),
        ));

        $result = $this->getPlayerService()->getVideoFilePlayer(array('convertParams' => array('hasVideoWatermark' => true), 'mcStatus' => 'yes', 'globalId' => 1), true, array(), true);
        $this->assertEquals('www.baidu.com', $result['mp4Url']);
        $this->assertEquals(1, $result['resId']);
        $this->assertEquals('te', $result['context']['subtitles'][0]['name']);
    }

    public function testGetVideoPlayUrl()
    {
        $this->mockBiz('MaterialLib:MaterialLibService', array(
            array('functionName' => 'player', 'returnValue' => array('subtitles' => array(array('name' => 'test.srt')), 'mp4url' => 'www.baidu.com')),
        ));

        $result = $this->getPlayerService()->getVideoPlayUrl(
            array('storage' => 'cloud', 'metas2' => true, 'convertParams' => array('convertor' => 'HLSEncryptedVideo'), 'id' => 1), array('hideBeginning' => true), true
        );
        $this->assertEquals('hls_playlist', $result['route']);
        $this->assertTrue($result['referenceType']);

        $this->mockBiz('MaterialLib:MaterialLibService', array(
            array('functionName' => 'player', 'returnValue' => array('url' => 'www.baidu.com')),
        ));
        $result = $this->getPlayerService()->getVideoPlayUrl(
            array('metas' => true, 'hashId' => 1, 'storage' => 'cloud', 'globalId' => 2), array(), true
        );
        $this->assertEquals('www.baidu.com', $result['url']);

        $result = $this->getPlayerService()->getVideoPlayUrl(
            array('id' => 1, 'storage' => 'es'), array(), true
        );
        $this->assertEquals('player_local_media', $result['route']);
        $this->assertTrue($result['referenceType']);
    }

    public function testGetDocFilePlayer()
    {
        $this->mockBiz('File:UploadFileService', array(
            array('functionName' => 'getFullFile', 'returnValue' => array(), 'withParams' => array(1)),
            array('functionName' => 'getFullFile', 'returnValue' => array('globalId' => 1, 'type' => 'document', 'convertStatus' => 'success'), 'withParams' => array(2)),
            array('functionName' => 'getFullFile', 'returnValue' => array('globalId' => 1, 'type' => 'document', 'convertStatus' => 'error'), 'withParams' => array(3)),
            array('functionName' => 'getFullFile', 'returnValue' => array('globalId' => 1, 'type' => 'document', 'convertStatus' => 'processing'), 'withParams' => array(4)),
        ));
        $this->mockBiz('MaterialLib:MaterialLibService', array(
            array('functionName' => 'player', 'returnValue' => array('url' => 'www.baidu.com')),
        ));

        $result = $this->getPlayerService()->getDocFilePlayer(array('mediaId' => 1), true);
        $this->assertEquals('抱歉，文档文件不存在，暂时无法学习。', $result[1]['message']);

        $result = $this->getPlayerService()->getDocFilePlayer(array('mediaId' => 2), true);
        $this->assertEquals('www.baidu.com', $result[0]['url']);

        $result = $this->getPlayerService()->getDocFilePlayer(array('mediaId' => 3), true);
        $this->assertEquals('文档转换失败，请到课程文件管理中，重新转换。', $result[1]['message']);

        $result = $this->getPlayerService()->getDocFilePlayer(array('mediaId' => 4), true);
        $this->assertEquals('文档还在转换中，还不能查看，请稍等。', $result[1]['message']);
    }

    /**
     * @expectedException \Biz\Player\PlayerException
     * @expectedExceptionMessage exception.player.file_type_invalid
     */
    public function testGetDocFilePlayerWithNotDocTyoe()
    {
        $this->mockBiz('File:UploadFileService', array(
            array('functionName' => 'getFullFile', 'returnValue' => array('globalId' => 1, 'type' => 'video')),
        ));

        $this->getPlayerService()->getDocFilePlayer(array('mediaId' => 1), true);
    }

    public function testGetPptFilePlayer()
    {
        $this->mockBiz('File:UploadFileService', array(
            array('functionName' => 'getFullFile', 'returnValue' => array(), 'withParams' => array(1)),
            array('functionName' => 'getFullFile', 'returnValue' => array('globalId' => 1, 'type' => 'ppt', 'convertStatus' => 'success'), 'withParams' => array(2)),
            array('functionName' => 'getFullFile', 'returnValue' => array('globalId' => 1, 'type' => 'ppt', 'convertStatus' => 'error'), 'withParams' => array(3)),
            array('functionName' => 'getFullFile', 'returnValue' => array('globalId' => 1, 'type' => 'ppt', 'convertStatus' => 'processing'), 'withParams' => array(4)),
        ));
        $this->mockBiz('MaterialLib:MaterialLibService', array(
            array('functionName' => 'player', 'returnValue' => array('url' => 'www.baidu.com')),
        ));

        $result = $this->getPlayerService()->getPptFilePlayer(array('mediaId' => 1), true);
        $this->assertEquals('抱歉，PPT文件不存在，暂时无法学习。', $result[1]['message']);

        $result = $this->getPlayerService()->getPptFilePlayer(array('mediaId' => 2), true);
        $this->assertEquals('www.baidu.com', $result[0]['url']);

        $result = $this->getPlayerService()->getPptFilePlayer(array('mediaId' => 3), true);
        $this->assertEquals('PPT文档转换失败，请到课程文件管理中，重新转换。', $result[1]['message']);

        $result = $this->getPlayerService()->getPptFilePlayer(array('mediaId' => 4), true);
        $this->assertEquals('PPT文档还在转换中，还不能查看，请稍等。', $result[1]['message']);
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
