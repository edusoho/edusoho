<?php

namespace Tests\Unit\Course\Service;

use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PlayerServiceTest extends BaseTestCase
{
    public function testGetAudioAndVideoPlayerType()
    {
        $file = ['type' => 'audio', 'storage' => 'local'];
        $result = $this->getPlayerService()->getAudioAndVideoPlayerType($file);
        $this->assertEquals('audio-player', $result);

        $file = ['type' => 'video', 'storage' => 'local'];
        $result = $this->getPlayerService()->getAudioAndVideoPlayerType($file);
        $this->assertEquals('local-video-player', $result);

        $file = ['type' => 'video', 'storage' => 'cloud'];
        $result = $this->getPlayerService()->getAudioAndVideoPlayerType($file);
        $this->assertEquals('balloon-cloud-video-player', $result);

        $file = ['type' => 'ppt', 'storage' => 'cloud'];
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
        $this->mockBiz('System:SettingService', [
            ['functionName' => 'get', 'returnValue' => ['enable_hls_encryption_plus' => true, 'video_header' => true, 'support_mobile' => 1]],
        ]);
        $this->mockBiz('File:UploadFileService', [
            ['functionName' => 'getFileByTargetType', 'returnValue' => ['convertStatus' => 'success', 'length' => 10]],
        ]);
        $this->mockBiz('MaterialLib:MaterialLibService', [
            ['functionName' => 'player', 'returnValue' => ['subtitles' => [['name' => 'test.srt']], 'mp4url' => 'www.baidu.com']],
        ]);

        $result = $this->getPlayerService()->getVideoFilePlayer(['convertParams' => ['hasVideoWatermark' => true], 'mcStatus' => 'yes', 'globalId' => 1, 'storage' => 'cloud'], true, [], true);
        $this->assertEquals('www.baidu.com', $result['mp4Url']);
        $this->assertEquals(1, $result['resId']);
        $this->assertEquals('te', $result['context']['subtitles'][0]['name']);
    }

    public function testGetVideoPlayUrl()
    {
        $this->mockBiz('MaterialLib:MaterialLibService', [
            ['functionName' => 'player', 'returnValue' => ['subtitles' => [['name' => 'test.srt']], 'mp4url' => 'www.baidu.com']],
        ]);

        $result = $this->getPlayerService()->getVideoPlayUrl(
            ['storage' => 'cloud', 'metas2' => true, 'convertParams' => ['convertor' => 'HLSEncryptedVideo'], 'id' => 1], ['hideBeginning' => true], true
        );
        $this->assertEquals('hls_playlist', $result['route']);
        $this->assertEquals(UrlGeneratorInterface::ABSOLUTE_URL, $result['referenceType']);

        $this->mockBiz('MaterialLib:MaterialLibService', [
            ['functionName' => 'player', 'returnValue' => ['url' => 'www.baidu.com']],
        ]);
        $result = $this->getPlayerService()->getVideoPlayUrl(
            ['metas' => true, 'hashId' => 1, 'storage' => 'cloud', 'globalId' => 2], [], true
        );
        $this->assertEquals('www.baidu.com', $result['url']);

        $result = $this->getPlayerService()->getVideoPlayUrl(
            ['id' => 1, 'storage' => 'es'], [], true
        );
        $this->assertEquals('player_local_media', $result['route']);
        $this->assertEquals(UrlGeneratorInterface::ABSOLUTE_URL, $result['referenceType']);
    }

    public function testGetDocFilePlayer()
    {
        $this->mockBiz('File:UploadFileService', [
            ['functionName' => 'getFullFile', 'returnValue' => [], 'withParams' => [1]],
            ['functionName' => 'getFullFile', 'returnValue' => ['globalId' => 1, 'type' => 'document', 'convertStatus' => 'success', 'storage' => 'cloud'], 'withParams' => [2]],
            ['functionName' => 'getFullFile', 'returnValue' => ['globalId' => 1, 'type' => 'document', 'convertStatus' => 'error', 'storage' => 'cloud'], 'withParams' => [3]],
            ['functionName' => 'getFullFile', 'returnValue' => ['globalId' => 1, 'type' => 'document', 'convertStatus' => 'processing', 'storage' => 'cloud'], 'withParams' => [4]],
        ]);
        $this->mockBiz('MaterialLib:MaterialLibService', [
            ['functionName' => 'player', 'returnValue' => ['url' => 'www.baidu.com']],
        ]);

        $result = $this->getPlayerService()->getDocFilePlayer(['mediaId' => 1], true);
        $this->assertEquals('抱歉，文档文件不存在，暂时无法学习。', $result[1]['message']);

        $result = $this->getPlayerService()->getDocFilePlayer(['mediaId' => 2], true);
        $this->assertEquals('www.baidu.com', $result[0]['url']);

        $result = $this->getPlayerService()->getDocFilePlayer(['mediaId' => 3], true);
        $this->assertEquals('文档转换失败，请到课程文件管理中，重新转换。', $result[1]['message']);

        $result = $this->getPlayerService()->getDocFilePlayer(['mediaId' => 4], true);
        $this->assertEquals('文档还在转换中，还不能查看，请稍等。', $result[1]['message']);
    }

    /**
     * @expectedException \Biz\Player\PlayerException
     * @expectedExceptionMessage exception.player.file_type_invalid
     */
    public function testGetDocFilePlayerWithNotDocTyoe()
    {
        $this->mockBiz('File:UploadFileService', [
            ['functionName' => 'getFullFile', 'returnValue' => ['globalId' => 1, 'type' => 'video']],
        ]);

        $this->getPlayerService()->getDocFilePlayer(['mediaId' => 1], true);
    }

    public function testGetPptFilePlayer()
    {
        $this->mockBiz('File:UploadFileService', [
            ['functionName' => 'getFullFile', 'returnValue' => ['globalId' => 1, 'type' => 'live', 'storage' => 'cloud'], 'withParams' => [1]],
            ['functionName' => 'getFullFile', 'returnValue' => ['globalId' => 1, 'type' => 'ppt', 'convertStatus' => 'success', 'storage' => 'cloud'], 'withParams' => [2]],
            ['functionName' => 'getFullFile', 'returnValue' => ['globalId' => 1, 'type' => 'ppt', 'convertStatus' => 'error', 'storage' => 'cloud'], 'withParams' => [3]],
            ['functionName' => 'getFullFile', 'returnValue' => ['globalId' => 1, 'type' => 'ppt', 'convertStatus' => 'processing', 'storage' => 'cloud'], 'withParams' => [4]],
        ]);
        $this->mockBiz('MaterialLib:MaterialLibService', [
            ['functionName' => 'player', 'returnValue' => ['url' => 'www.baidu.com']],
        ]);

        $result = $this->getPlayerService()->getPptFilePlayer(['mediaId' => 1], true);
        $this->assertEquals('抱歉，PPT文件不存在，暂时无法学习。', $result[1]['message']);

        $result = $this->getPlayerService()->getPptFilePlayer(['mediaId' => 2], true);
        $this->assertEquals('www.baidu.com', $result[0]['url']);

        $result = $this->getPlayerService()->getPptFilePlayer(['mediaId' => 3], true);
        $this->assertEquals('PPT文档转换失败，请到课程文件管理中，重新转换。', $result[1]['message']);

        $result = $this->getPlayerService()->getPptFilePlayer(['mediaId' => 4], true);
        $this->assertEquals('PPT文档还在转换中，还不能查看，请稍等。', $result[1]['message']);
    }

    public function testMakeToken($value = '')
    {
        $result = ReflectionUtils::invokeMethod($this->getPlayerService(),
            'makeToken',
            [
                'hls.playlist',
                1,
                ['watchTimeLimit' => 5, 'hideBeginning' => true],
            ]
        );
        $this->assertEquals('hls.playlist', $result['type']);
        $this->assertEquals(10, $result['times']);
        $this->assertArrayEquals(
            ['id' => 1, 'watchTimeLimit' => 5, 'hideBeginning' => true],
            $result['data']
        );
    }

    protected function getPlayerService()
    {
        return $this->createService('Player:PlayerService');
    }
}
