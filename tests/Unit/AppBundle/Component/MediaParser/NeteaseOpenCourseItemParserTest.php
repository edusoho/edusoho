<?php

namespace Tests\Unit\AppBundle\Component\MediaParser;

use Biz\BaseTestCase;
use AppBundle\Component\MediaParser\ItemParser\NeteaseOpenCourseItemParser;

class NeteaseOpenCourseItemParserTest extends BaseTestCase
{
    public function testParse()
    {
        $video = $this->createParser()->parse('http://v.163.com/movie/2013/7/N/S/M941471K5_M9414FGNS.html');

        $this->assertEquals('video', $video['type']);
        $this->assertEquals('NeteaseOpenCourse', $video['source']);
        $this->assertArrayHasKey('uuid', $video);
        $this->assertArrayHasKey('name', $video);
        $this->assertArrayHasKey('page', $video);
        $this->assertArrayHasKey('files', $video);

        $file = empty($video['files']) ? array() : $video['files'][0];
        $this->assertEquals('swf', $file['type']);
        $this->assertStringStartsWith('http://', $file['url']);
    }

    private function createParser()
    {
        return new NeteaseOpenCourseItemParser();
    }
}
