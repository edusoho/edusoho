<?php

namespace Tests\Unit\AppBundle\Component\MediaParser;

use Biz\BaseTestCase;
use AppBundle\Component\MediaParser\ItemParser\QQVideoItemParser;
use AppBundle\Component\MediaParser\ParseException;

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
    }

    private function createParser()
    {
        return new QQVideoItemParser();
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
}
