<?php

namespace Tests\Unit\AppBundle\Component\MediaParser\ItemParser;

use Biz\BaseTestCase;
use AppBundle\Component\MediaParser\ItemParser\LetvYunItemParser;
use AppBundle\Common\ReflectionUtils;

class LetvYunItemParserTest extends BaseTestCase
{
    /**
     * @expectedException \AppBundle\Component\MediaParser\ParserException
     */
    public function testParseWithError()
    {
        $mockedSender = $this->mockBiz(
            'Mocked:MockedSender',
            array(
                array(
                    'functionName' => 'fetchUrl',
                    'withParams' => array('http://letv/special/M941471K5_M9414FGNS.html'),
                    'returnValue' => array(),
                ),
            )
        );
        $video = $this->createParser()->parse('http://letv/special/M941471K5_M9414FGNS.html');
    }

    public function testParse()
    {
        $parser = $this->createParser();

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
                    'withParams' => array('http://v.163.com/special/M941471K5_M9414FGNS.html?width=640&height=360'),
                    'returnValue' => array(
                        'code' => 200,
                        'content' => iconv('utf-8', 'gb2312', $content),
                    ),
                ),
            )
        );
        $parser = ReflectionUtils::setProperty($parser, 'mockedSender', $mockedSender);

        $video = $parser->parse('http://v.163.com/special/M941471K5_M9414FGNS.html?width=640&height=360');

        $mockedSender->shouldHaveReceived('fetchUrl')->times(1);
        $this->assertArrayEquals(
            array(
                'type' => 'video',
                'source' => 'letv',
                'files' => array(array(
                    'url' => 'http://v.163.com/special/M941471K5_M9414FGNS.html?width=100%&height=100%',
                    'type' => 'swf',
                )),
            ),
            $video
        );
    }

    public function testDetect()
    {
        $this->assertTrue($this->createParser()->detect('http://yuntv.letv.com/bcloud.html'));
        $this->assertFalse($this->createParser()->detect('http://yuntv.letv.com/Ccloud.html'));
    }

    private function createParser()
    {
        return new LetvYunItemParser();
    }
}
