<?php
namespace Topxia\Component\MediaParser\Tests;

use Topxia\Component\MediaParser\ItemParser\QQVideoItemParser;

class QQVideoItemParserTest extends \PHPUnit_Framework_TestCase
{
    public function testParse()
    {
        $video = $this->createParser()->parse('http://v.qq.com/cover/0/0i17jjqo3piy5h8.html?vid=l0011p22hob');
        $this->assertEquals('video', $video['type']);
        $this->assertEquals('qqvideo', $video['source']);
        $this->assertEquals('qqvideo:l0011p22hob', $video['uuid']);
        $this->assertArrayHasKey('name', $video);
        $this->assertArrayHasKey('page', $video);
        $this->assertArrayHasKey('files', $video);

        $video = $this->createParser()->parse('http://v.qq.com/boke/page/h/0/5/h0150zpvfq5.html');
        $this->assertEquals('video', $video['type']);
        $this->assertEquals('qqvideo', $video['source']);
        $this->assertEquals('qqvideo:h0150zpvfq5', $video['uuid']);
        $this->assertArrayHasKey('name', $video);
        $this->assertArrayHasKey('page', $video);
        $this->assertArrayHasKey('files', $video);

        $video = $this->createParser()->parse('http://v.qq.com/page/w/1/e/w0168yk7k1e.html');
        $this->assertEquals('video', $video['type']);
        $this->assertEquals('qqvideo', $video['source']);
        $this->assertEquals('qqvideo:w0168yk7k1e', $video['uuid']);
        $this->assertArrayHasKey('name', $video);
        $this->assertArrayHasKey('page', $video);
        $this->assertArrayHasKey('files', $video);

    }

    private function createParser()
    {
        return new QQVideoItemParser();
    }
}