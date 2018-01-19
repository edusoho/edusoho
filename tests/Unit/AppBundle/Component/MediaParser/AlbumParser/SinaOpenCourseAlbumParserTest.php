<?php

namespace Tests\Unit\AppBundle\Component\MediaParser\AlbumParser;

use Biz\BaseTestCase;
use AppBundle\Component\MediaParser\AlbumParser\SinaOpenCourseAlbumParser;
use AppBundle\Common\ReflectionUtils;

class SinaOpenCourseAlbumParserTest extends BaseTestCase
{
    /**
     * @expectedException \AppBundle\Component\MediaParser\ParseException
     */
    public function testParseWithError()
    {
        $parser = new SinaOpenCourseAlbumParser();
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
        $parser = new SinaOpenCourseAlbumParser();

        $mockedSender = $this->mockBiz(
            'Mocked:MockedSender',
            array(
                array(
                    'functionName' => 'fetchUrl',
                    'withParams' => array('http://open.sina.com.cn/course/id_1122'),
                    'returnValue' => array(
                        'code' => 200,
                        'content' => '<h2 class="fblue">blue(3)</h2><p class="txt">summary show</p>'.
                                     '<div class="container2">'.
                                       '<ul>'.
                                         '<li><img alt="<a href=\"hypelink\"" src="hyperlinkSrc"</li>'.
                                       '</ul>'.
                                     '</div>',
                    ),
                ),
            )
        );
        $parser = ReflectionUtils::setProperty($parser, 'mockedSender', $mockedSender);
        $video = $parser->parse('http://open.sina.com.cn/course/id_1122');

        $mockedSender->shouldHaveReceived('fetchUrl')->times(1);

        $this->assertArrayEquals(
            array(
                'id' => 'course_1122',
                'uuid' => 'SinaOpenCourseAlbum:course_1122',
                'title' => 'blue',
                'summary' => 'summary show',
                'items' => array(
                    array(
                        'url' => 'http://open.sina.com.cn/course/id_1122',
                        'title' => '<a href=\\',
                        'picture' => 'hyperlinkSrc',
                    ),
                ),
            ),
            $video
        );
    }

    public function testDetect()
    {
        $parser = new SinaOpenCourseAlbumParser();
        $this->assertTrue($parser->detect('http://open.sina.com.cn/course/id_1122'));
        $this->assertFalse($parser->detect('http://open.sina.com.cn/course/id2_1122'));
    }
}
