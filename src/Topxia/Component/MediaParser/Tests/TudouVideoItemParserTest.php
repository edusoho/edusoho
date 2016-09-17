<?php
namespace Topxia\Component\MediaParser\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Component\MediaParser\ItemParser\TudouVideoItemParser;

class TudouVideoItemParserTest extends BaseTestCase
{
    public function testParse()
    {
        $video = $this->createParser()->parse('http://www.tudou.com/listplay/rXvJPdf6Nao/kiG-UTshs5Y.html');

        $this->assertEquals('video', $video['type']);
        $this->assertEquals('tudou', $video['source']);
        $this->assertEquals('tudou:kiG-UTshs5Y', $video['uuid']);
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