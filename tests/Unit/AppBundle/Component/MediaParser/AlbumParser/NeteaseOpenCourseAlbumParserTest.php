<?php

namespace Tests\Unit\AppBundle\Component\MediaParser\AlbumParser;

use Biz\BaseTestCase;
use AppBundle\Component\MediaParser\AlbumParser\NeteaseOpenCourseAlbumParser;
use AppBundle\Common\ReflectionUtils;

class NeteaseOpenCourseAlbumParserTest extends BaseTestCase
{
    /**
     * @expectedException \AppBundle\Component\MediaParser\ParseException
     */
    public function testParseWithError()
    {
        $parser = new NeteaseOpenCourseAlbumParser();
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
        $parser = new NeteaseOpenCourseAlbumParser();

        $content = '<title>课程</title>'.
            '<span class="m-cintro"/><img src="a.html" />'.
            '<h2>本课程共20分钟</h2>'.
            '<p>课程介绍</p><p>abcdefg</p>'.
            '<h2>课程介绍</h2>221w<span class="cdblan">(dfsf)</span>'.
            '<id="lession-list"><h3><a href="bcd">ok</a></ul>';

        $mockedSender = $this->mockBiz(
            'Mocked:MockedSender',
            array(
                array(
                    'functionName' => 'fetchUrl',
                    'withParams' => array('http://v.163.com/special/M941471K5_M9414FGNS.html'),
                    'returnValue' => array(
                        'code' => 200,
                        'content' => iconv('utf-8', 'gb2312', $content),
                    ),
                ),
            )
        );
        $parser = ReflectionUtils::setProperty($parser, 'mockedSender', $mockedSender);

        $video = $parser->parse('http://v.163.com/special/M941471K5_M9414FGNS.html');

        $mockedSender->shouldHaveReceived('fetchUrl')->times(1);
        $this->assertArrayEquals(
            array(
                'id' => 'M941471K5_M9414FGNS.htm',
                'uuid' => 'NeteaseOpenCourseAlbum:M941471K5_M9414FGNS.htm',
                'title' => '',
                'summary' => '(dfsf)',
                'picture' => '',
                'items' => array(array(
                    'id' => false,
                    'title' => 'ok',
                    'url' => 'bcd',
                )),
                'number' => 1,
            ),
            $video
        );
    }
}
