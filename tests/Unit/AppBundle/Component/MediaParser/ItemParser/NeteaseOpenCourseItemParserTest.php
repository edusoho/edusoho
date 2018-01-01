<?php

namespace Tests\Unit\AppBundle\Component\MediaParser\ItemParser;

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

    public function testDetect()
    {
        $this->assertTrue($this->createParser()->detect('http://v.163.com/movie/abc.html'));
        $this->assertTrue($this->createParser()->detect('http://open.163.com/movie/a.html'));
        $this->assertFalse($this->createParser()->detect('http://open.164.com/movie/a.html'));
        $this->assertFalse($this->createParser()->detect('http://open.163.com/movie/a.mp3'));
        $this->assertFalse($this->createParser()->detect('http://v.163.com/movie/a.mp3'));
    }

    private function createParser()
    {
        return new NeteaseOpenCourseItemParser();
    }
}
