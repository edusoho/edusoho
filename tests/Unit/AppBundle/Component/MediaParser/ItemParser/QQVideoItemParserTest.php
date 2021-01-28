<?php

namespace Tests\Unit\AppBundle\Component\MediaParser\ItemParser;

use Biz\BaseTestCase;
use AppBundle\Component\MediaParser\ItemParser\QQVideoItemParser;
use AppBundle\Component\MediaParser\ParseException;
use AppBundle\Common\ReflectionUtils;

class QQVideoItemParserTest extends BaseTestCase
{
    public function testParse()
    {
        $video = $this->parseQqItem('http://v.qq.com/cover/0/0i17jjqo3piy5h8.html?vid=l0011p22hob');
        $this->assertEquals('qqvideo:l0011p22hob', $video['uuid']);

        $video = $this->parseQqItem('http://v.qq.com/x/page/h0150zpvfq5.html');
        $this->assertEquals('qqvideo:h0150zpvfq5', $video['uuid']);

        $video = $this->parseQqItem('http://v.qq.com/x/page/w0168yk7k1e.html');
        $this->assertEquals('qqvideo:w0168yk7k1e', $video['uuid']);

        $video = $this->parseQqItem('http://v.qq.com/x/cover/32kshc0e1wcifxj/n0329caqnyf.html');
        $this->assertEquals('qqvideo:n0329caqnyf', $video['uuid']);

        $this->verifyShouldHaveReceived();
    }

    private function createParser()
    {
        if (empty($this->parser)) {
            $this->parser = new QQVideoItemParser();

            $path = $this->biz['kernel.root_dir'].'/../tests/Unit/AppBundle/Component/MediaParser/ItemParser/';
            $mockedPaths = $this->getMockedPaths();

            $mockedFuncInfos = array();
            foreach ($mockedPaths as $url => $fileName) {
                $mockedFuncInfos[] = array(
                    'functionName' => 'fetchUrl',
                    'withParams' => array($url),
                    'returnValue' => array(
                        'code' => 200,
                        'content' => file_get_contents($path.$fileName),
                    ),
                );
            }

            $mockedSender = $this->mockBiz(
                'Mocked:MockedQQVideoSender',
                $mockedFuncInfos
            );

            $this->parser = ReflectionUtils::setProperty($this->parser, 'mockedSender', $mockedSender);
        }

        return $this->parser;
    }

    private function verifyShouldHaveReceived()
    {
        $mockedSender = ReflectionUtils::getProperty($this->createParser(), 'mockedSender');

        $mockedPaths = $this->getMockedPaths();
        foreach ($mockedPaths as $url => $fileName) {
            $mockedSender->shouldHaveReceived('fetchUrl', array($url));
        }
    }

    private function parseQqItem($url)
    {
        $video = $this->parseAgainIfFailed($url);
        $this->assertEquals('video', $video['type']);
        $this->assertEquals('qqvideo', $video['source']);
        $this->assertArrayHasKey('name', $video);
        $this->assertArrayHasKey('page', $video);
        $this->assertArrayHasKey('files', $video);

        return $video;
    }

    private function parseAgainIfFailed($url)
    {
        try {
            return $this->createParser()->parse($url);
        } catch (ParseException $e) {
            sleep(rand(1, 8));  //延后 1 ~ 8秒
            return $this->createParser()->parse($url);
        }
    }

    private function getMockedPaths()
    {
        return array(
            'http://v.qq.com/x/page/h0150zpvfq5.html' => 'QQVideoResponse2.md',
            'http://v.qq.com/x/page/w0168yk7k1e.html' => 'QQVideoResponse3.md',
            'http://v.qq.com/x/cover/32kshc0e1wcifxj/n0329caqnyf.html' => 'QQVideoResponse4.md',
            'http://sns.video.qq.com/tvideo/fcgi-bin/video?otype=json&vid=l0011p22hob' => 'QQVideoResponse1_sns.md',
            'http://sns.video.qq.com/tvideo/fcgi-bin/video?otype=json&vid=h0150zpvfq5' => 'QQVideoResponse2_sns.md',
            'http://sns.video.qq.com/tvideo/fcgi-bin/video?otype=json&vid=w0168yk7k1e' => 'QQVideoResponse3_sns.md',
            'http://sns.video.qq.com/tvideo/fcgi-bin/video?otype=json&vid=n0329caqnyf' => 'QQVideoResponse4_sns.md',
        );
    }
}
