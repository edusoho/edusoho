<?php

namespace Tests\Unit\AppBundle\Component\MediaParser\AlbumParser;

use Biz\BaseTestCase;
use AppBundle\Component\MediaParser\AlbumParser\QQVideoAlbumParser;
use AppBundle\Common\ReflectionUtils;

class QQVideoAlbumParserTest extends BaseTestCase
{
    /**
     * @expectedException \AppBundle\Component\MediaParser\ParseException
     */
    public function testParseWithError()
    {
        $parser = new QQVideoAlbumParser();
        $mockedSender = $this->mockBiz(
            'Mocked:MockedSender',
            array(
                array(
                    'functionName' => 'fetchUrl',
                    'withParams' => array('http://v.163.com/special/M941471K5_M9414FGNS.html'),
                    'returnValue' => array(),
                ),
            )
        );
        $parser = ReflectionUtils::setProperty($parser, 'mockedSender', $mockedSender);

        $video = $parser->parse('http://v.163.com/special/M941471K5_M9414FGNS.html');
    }

    public function testParse()
    {
        $parser = new QQVideoAlbumParser();

        $mockedSender = $this->mockBiz(
            'Mocked:MockedSender',
            array(
                array(
                    'functionName' => 'fetchUrl',
                    'withParams' => array('http://www.youku.com/playlist_show/id_121'),
                    'returnValue' => array(
                        'code' => 200,
                        'content' => '<div>COVER_INFO, this is title:"content", id:"book"</div><div id="mod_videolist"><div class="mod_cont"><ul><li id="li_1">li-content<li></ul></div></div>',
                    ),
                ),
            )
        );
        $parser = ReflectionUtils::setProperty($parser, 'mockedSender', $mockedSender);
        $video = $parser->parse('http://www.youku.com/playlist_show/id_121');

        $mockedSender->shouldHaveReceived('fetchUrl')->times(1);

        $this->assertArrayEquals(
            array(
                'id' => 'book',
                'uuid' => 'QQVideoAlbum:book',
                'title' => 'content',
                'url' => 'http://v.qq.com/cover/b/book.html',
                'items' => array(
                    array(
                        'id' => '1',
                        'url' => 'http://v.qq.com/cover/b/book.html?vid=1',
                    ),
                ),
            ),
            $video
        );
    }
}
