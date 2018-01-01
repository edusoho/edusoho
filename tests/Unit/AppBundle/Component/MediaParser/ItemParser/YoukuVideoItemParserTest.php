<?php

namespace Tests\Unit\AppBundle\Component\MediaParser\ItemParser;

use Biz\BaseTestCase;
use AppBundle\Component\MediaParser\ItemParser\YoukuVideoItemParser;

class YoukuVideoItemParserTest extends BaseTestCase
{
    public function testParse()
    {
        $video = $this->createParser()->parse('http://v.youku.com/v_show/id_XNTgxOTA5ODg0.html');

        $this->assertEquals('video', $video['type']);
        $this->assertEquals('youku', $video['source']);
        $this->assertEquals('youku:XNTgxOTA5ODg0', $video['uuid']);
        $this->assertArrayHasKey('name', $video);
        $this->assertArrayHasKey('page', $video);
        $this->assertArrayHasKey('pictures', $video);
        $this->assertArrayHasKey('files', $video);

        $file = empty($video['files']) ? array() : $video['files'][0];
        $this->assertEquals('swf', $file['type']);
        $this->assertStringStartsWith('//', $file['url']);
    }

    private function createParser()
    {
        return new YoukuVideoItemParser();
    }
}
