<?php

namespace Tests\Unit\AppBundle\Component\MediaParser\ItemParser;

use AppBundle\Common\ReflectionUtils;
use AppBundle\Component\MediaParser\ItemParser\YoukuVideoItemParser;
use Biz\BaseTestCase;

class YoukuVideoItemParserTest extends BaseTestCase
{
    public function testParse()
    {
        $parser = $this->createParser();

        $youkuResponse = file_get_contents($this->biz['kernel.root_dir'].
                '/../tests/Unit/AppBundle/Component/MediaParser/ItemParser/YoukuResponse.md');
        $mockedSender = $this->mockBiz(
            'Mocked:MockedYoukuSender',
            [
                [
                    'functionName' => 'fetchUrl',
                    'withParams' => ['http://v.youku.com/v_show/id_XNTgxOTA5ODg0.html'],
                    'returnValue' => [
                        'code' => 200,
                        'content' => $youkuResponse,
                    ],
                ],
            ]
        );

        $parser = ReflectionUtils::setProperty($parser, 'mockedSender', $mockedSender);
        $video = $parser->parse('http://v.youku.com/v_show/id_XNTgxOTA5ODg0.html');

        $this->assertEquals('video', $video['type']);
        $this->assertEquals('youku', $video['source']);
        $this->assertEquals('youku:XNTgxOTA5ODg0', $video['uuid']);
        $this->assertArrayHasKey('name', $video);
        $this->assertArrayHasKey('page', $video);
        $this->assertArrayHasKey('pictures', $video);
        $this->assertArrayHasKey('files', $video);

        $file = empty($video['files']) ? [] : $video['files'][0];
        $this->assertEquals('mp4', $file['type']);
        $this->assertStringStartsWith('https://', $file['url']);

        $mockedSender->shouldHaveReceived('fetchUrl');
    }

    private function createParser()
    {
        return new YoukuVideoItemParser();
    }
}
