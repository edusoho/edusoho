<?php

namespace Tests\Unit\AppBundle\Component\MediaParser\AlbumParser;

use Biz\BaseTestCase;
use AppBundle\Component\MediaParser\AlbumParser\YoukuVideoAlbumParser;
use AppBundle\Common\ReflectionUtils;

class YoukuVideoAlbumParserTest extends BaseTestCase
{
    /**
     * @expectedException \AppBundle\Component\MediaParser\ParseException
     */
    public function testParseWithError()
    {
        $parser = new YoukuVideoAlbumParser();
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
        $parser = new YoukuVideoAlbumParser();

        $mockedSender = $this->mockBiz(
            'Mocked:MockedSender',
            array(
                array(
                    'functionName' => 'fetchUrl',
                    'withParams' => array('http://www.youku.com/playlist_show/id_121'),
                    'returnValue' => array(
                        'code' => 200,
                        'content' => '视频: <span class="num">123</span><span><h1 class="title"><div class="name">this is class content</div></h1></span>',
                    ),
                ),
                array(
                    'functionName' => 'fetchUrl',
                    'withParams' => array('http://v.youku.com/v_vpvideoplaylistv5?pl=50&f=121&pn=1'),
                    'returnValue' => array(
                        'code' => 200,
                        'content' => '<div id="item_id1"><img data_src="b.png"><span class="l_title">l title 1</span></img><span class="l_time"><em class="num">l_time 1</em></span>',
                    ),
                ),
                array(
                    'functionName' => 'fetchUrl',
                    'withParams' => array('http://v.youku.com/v_vpvideoplaylistv5?pl=50&f=121&pn=2'),
                    'returnValue' => array(
                        'code' => 200,
                        'content' => '<div id="item_id2"><img data_src="b.png"><span class="l_title">l title 2</span></img><span class="l_time"><em class="num">l_time 2</em></span>',
                    ),
                ),
                array(
                    'functionName' => 'fetchUrl',
                    'withParams' => array('http://v.youku.com/v_vpvideoplaylistv5?pl=50&f=121&pn=3'),
                    'returnValue' => array(
                        'code' => 200,
                        'content' => '<div id="item_id3"><img data_src="b.png"><span class="l_title">l title 3</span></img><span class="l_time"><em class="num">l_time 3</em></span>',
                    ),
                ),
            )
        );
        $parser = ReflectionUtils::setProperty($parser, 'mockedSender', $mockedSender);
        $video = $parser->parse('http://www.youku.com/playlist_show/id_121');

        $mockedSender->shouldHaveReceived('fetchUrl')->times(4);
        $this->assertArrayEquals(
            array(
                'id' => '121',
                'uuid' => 'YoukuVideoAlbum:121',
                'title' => 'this is class content</div></h1>',
                'number' => '123',
                'items' => array(
                    array(
                        'id' => 'id1',
                        'uuid' => 'YoukuVideo:id1',
                        'url' => 'http://v.youku.com/v_show/id_id1.html',
                        'picture' => 'b.png',
                        'title' => 'l title 1',
                        'length' => 'l_time 1',
                    ),
                    array(
                        'id' => 'id2',
                        'uuid' => 'YoukuVideo:id2',
                        'url' => 'http://v.youku.com/v_show/id_id2.html',
                        'picture' => 'b.png',
                        'title' => 'l title 2',
                        'length' => 'l_time 2',
                    ),
                    array(
                        'id' => 'id3',
                        'uuid' => 'YoukuVideo:id3',
                        'url' => 'http://v.youku.com/v_show/id_id3.html',
                        'picture' => 'b.png',
                        'title' => 'l title 3',
                        'length' => 'l_time 3',
                    ),
                ),
            ),
            $video
        );
    }

    public function testDetect()
    {
        $parser = new YoukuVideoAlbumParser();
        $this->assertTrue($parser->parse('http://www.youku.com/playlist_show/id_121'));
        $this->assertFalse($parser->parse('http://www.youku.com/playlist_show/id1_121'));
    }
}
