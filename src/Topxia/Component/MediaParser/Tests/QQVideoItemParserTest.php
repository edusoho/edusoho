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
        $this->assertArrayHasKey('pictures', $video);
        $this->assertArrayHasKey('files', $video);

        $file = empty($video['files']) ? array() : $video['files'][0];
        $this->assertEquals('swf', $file['type']);
        $this->assertStringStartsWith('http://', $file['url']);

    }

    private function createParser()
    {
        return new QQVideoItemParser();
    }
}