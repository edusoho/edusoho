<?php

namespace Tests\Unit\AppBundle\Component\MediaParser\ItemParser;

use Biz\BaseTestCase;
use AppBundle\Component\MediaParser\ItemParser\FallbackItemParser;

class FallbackItemParserTest extends BaseTestCase
{
    public function testParse()
    {
        $video = $this->createParser()->parse('http://fall-back.item.parse/v_show/id_XNTgxOTA5ODg0.html');
        $this->assertArrayEquals(
            array(
                'id' => '4912b4db4ef729e27045d8dc233e1c9f',
                'uuid' => 'Fallback:4912b4db4ef729e27045d8dc233e1c9f',
                'type' => 'video',
                'title' => '',
                'url' => 'http://fall-back.item.parse/v_show/id_XNTgxOTA5ODg0.html',
                'swf_url' => 'http://fall-back.item.parse/v_show/id_XNTgxOTA5ODg0.html',
            ),
            $video
        );
    }

    public function testDetect()
    {
        $this->assertTrue($this->createParser()->detect('https://www.baidu.com/a.swf'));
        $this->assertTrue($this->createParser()->detect('http://www.baidu.com/a.mp4'));
    }

    private function createParser()
    {
        return new FallbackItemParser();
    }
}
