<?php

namespace Tests\Unit\AppBundle\Component\MediaParser;

use Biz\BaseTestCase;
use AppBundle\Component\MediaParser\MediaAttrsRender;
use AppBundle\Common\TimeMachine;

class MediaAttrsRenderTest extends BaseTestCase
{
    public function testRenderWithEmptyMedia()
    {
        $media = array();
        $result = MediaAttrsRender::render($media);

        $this->assertEquals('[]', $result);
    }

    public function testRenderYoukuVideo()
    {
        $media = array('id' => 367584, 'uuid' => 'YoukuVideo', 'swf_url' => 'http://www.edusoho.com/');

        $startTime = 1515303348;
        TimeMachine::setMockedTime($startTime);

        $result = MediaAttrsRender::render($media);
        $resultObj = json_decode($result, true);
        $this->assertArrayEquals(
            array(
                'swf_url' => 'http://www.edusoho.com/',
                'apple_url' => 'http://v.youku.com/player/getM3U8/vid/367584/ts/1515303348/v.m3u8',
            ),
            $resultObj
        );
    }

    public function testRenderQQVideo()
    {
        $media = array('id' => 367584, 'uuid' => 'QQVideo', 'swf_url' => 'http://www.edusoho.com/');

        $result = MediaAttrsRender::render($media);
        $resultObj = json_decode($result, true);
        $this->assertArrayEquals(
            array(
                'swf_url' => 'http://www.edusoho.com/',
                'mp4_url' => 'http://video.store.qq.com/367584.mp4',
            ),
            $resultObj
        );
    }

    public function testRenderOtherVideo()
    {
        $media = array(
            'id' => 367584,
            'uuid' => 'otherVideo',
            'mp4_url' => 'http://www.edusoho.com/a.mp4',
            'swf_url' => 'http://www.edusoho.com/',
            'apple_url' => 'http://www.edusoho.com/appleUrl.mp4',
        );

        $result = MediaAttrsRender::render($media);
        $resultObj = json_decode($result, true);
        $this->assertArrayEquals(
            array(
                'swf_url' => 'http://www.edusoho.com/',
                'mp4_url' => 'http://www.edusoho.com/a.mp4',
                'apple_url' => 'http://www.edusoho.com/appleUrl.mp4',
            ),
            $resultObj
        );
    }
}
