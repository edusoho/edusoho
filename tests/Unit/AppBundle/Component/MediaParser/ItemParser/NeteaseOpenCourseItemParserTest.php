<?php

namespace Tests\Unit\AppBundle\Component\MediaParser\ItemParser;

use AppBundle\Component\MediaParser\ItemParser\NeteaseOpenCourseItemParser;
use Biz\BaseTestCase;

class NeteaseOpenCourseItemParserTest extends BaseTestCase
{
    public function testParse()
    {
        // $video = $this->createParser()->parse('http://open.163.com/newview/movie/free?pid=MEM5RR44O&mid=MEM5RTLOA');

        // $this->assertEquals('video', $video['type']);
        // $this->assertEquals('NeteaseOpenCourse', $video['source']);
        // $this->assertArrayHasKey('uuid', $video);
        // $this->assertArrayHasKey('name', $video);
        // $this->assertArrayHasKey('page', $video);
        // $this->assertArrayHasKey('files', $video);

        // $file = empty($video['files']) ? array() : $video['files'][0];

        // $this->assertEquals('swf', $file['type']);
        // $this->assertStringStartsWith('//open.163.com/', $file['url']);
    }

    public function testDetect()
    {
        $this->assertTrue($this->createParser()->detect('https://v.163.com/movie/abc.html'));
        $this->assertTrue($this->createParser()->detect('https://open.163.com/movie/a.html'));
        $this->assertFalse($this->createParser()->detect('https://open.164.com/movie/a.html'));
    }

    private function createParser()
    {
        return new NeteaseOpenCourseItemParser();
    }
}
