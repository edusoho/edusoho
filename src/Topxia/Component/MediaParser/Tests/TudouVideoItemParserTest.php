<?php
namespace Topxia\Component\MediaParser\Tests;

use Topxia\Component\MediaParser\ItemParser\TudouVideoItemParser;

class TudouVideoItemParserTest extends \PHPUnit_Framework_TestCase
{
    public function testParse()
    {
        $video = $this->createParser()->parse('http://www.tudou.com/programs/view/c9ssb3rACb0/');

        $this->assertEquals('video', $video['type']);
        $this->assertEquals('tudou', $video['source']);
        $this->assertEquals('tudou:c9ssb3rACb0', $video['uuid']);
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
        return new TudouVideoItemParser();
    }
}