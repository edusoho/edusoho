<?php

namespace Tests\Unit\AppBundle\Component\MediaParser;

use Biz\BaseTestCase;
use AppBundle\Component\MediaParser\ParserProxy;
use Topxia\Service\Common\ServiceKernel;
use AppBundle\Common\ReflectionUtils;

class ParserProxyTest extends BaseTestCase
{
    public function testParseQQItem()
    {
        $proxy = new ParserProxy();
        $result = $proxy->parseItem('http://v.qq.com/cover/0/0i17jjqo3piy5h8.html?vid=l0011p22hob');

        $this->assertEquals('qqvideo:l0011p22hob', $result['uuid']);
    }

    public function testParseQQItemInServiceKernel()
    {
        $mockedParameterBag = $this->mockBiz(
            'Mock:ParameterBag',
            array(
                array(
                    'functionName' => 'has',
                    'withParams' => array('media_parser'),
                    'returnValue' => true,
                ),
                array(
                    'functionName' => 'get',
                    'withParams' => array('media_parser'),
                    'returnValue' => array(
                        'item' => array(array(
                            'class' => 'AppBundle\Component\MediaParser\ItemParser\QQVideoItemParser',
                        )),
                    ),
                ),
            )
        );
        $kernel = ServiceKernel::instance();
        $parameterBag = ReflectionUtils::getProperty($kernel, 'parameterBag');
        $kernel->setParameterBag($mockedParameterBag);
        $proxy = new ParserProxy();
        $result = $proxy->parseItem('http://v.qq.com/cover/0/0i17jjqo3piy5h8.html?vid=l0011p22hob');

        $this->assertEquals('qqvideo:l0011p22hob', $result['uuid']);
        $mockedParameterBag->shouldHaveReceived('get')->times(1);
        $mockedParameterBag->shouldHaveReceived('has')->times(1);

        $kernel->setParameterBag($parameterBag);
    }

    public function testPrepareMediaUriWithSelf()
    {
        $video = array('mediaSource' => 'self');
        $proxy = new ParserProxy();
        $result = $proxy->prepareMediaUri($video);
        $this->assertArrayEquals($video, $result);
    }

    /**
     * @expectedException \AppBundle\Component\MediaParser\ParserException
     */
    public function testPrepareMediaUriWithUnsupportedParser()
    {
        $proxy = new ParserProxy();
        $proxy->prepareMediaUri(array('mediaSource' => 'test'));
    }

    public function testPrepareMediaUriWithNeteaseOpenCourse()
    {
        $video = array('mediaSource' => 'NeteaseOpenCourse');

        $mockedParser = $this->mockBiz(
            'youkuParser',
            array(
                array(
                    'functionName' => 'prepareMediaUri',
                    'withParams' => array($video),
                    'returnValue' => array('url' => 'NeteaseOpenCourseUrl'),
                ),
            )
        );
        $proxy = new ParserProxy();
        ReflectionUtils::setProperty($proxy, 'mockedParser', $mockedParser);

        $result = $proxy->prepareMediaUri($video);

        $this->assertEquals('NeteaseOpenCourseUrl', $result['url']);
    }

    public function testPrepareMediaUriWithQqVideo()
    {
        $video = array('mediaSource' => 'qqvideo');

        $mockedParser = $this->mockBiz(
            'youkuParser',
            array(
                array(
                    'functionName' => 'prepareMediaUri',
                    'withParams' => array($video),
                    'returnValue' => array('url' => 'qqvideoUrl'),
                ),
            )
        );
        $proxy = new ParserProxy();
        ReflectionUtils::setProperty($proxy, 'mockedParser', $mockedParser);

        $result = $proxy->prepareMediaUri($video);

        $this->assertEquals('qqvideoUrl', $result['url']);
    }

    public function testPrepareYoukuMediaUri()
    {
        $video = array('mediaSource' => 'youku');

        $mockedParser = $this->mockBiz(
            'youkuParser',
            array(
                array(
                    'functionName' => 'prepareMediaUri',
                    'withParams' => array($video),
                    'returnValue' => array('url' => 'youkuUrl'),
                ),
            )
        );
        $proxy = new ParserProxy();
        ReflectionUtils::setProperty($proxy, 'mockedParser', $mockedParser);

        $result = $proxy->prepareYoukuMediaUri($video);

        $this->assertEquals('youkuUrl', $result['url']);
    }

    public function testPrepareYoukuMediaUriWithSelf()
    {
        $video = array('mediaSource' => 'self');

        $proxy = new ParserProxy();

        $result = $proxy->prepareYoukuMediaUri($video);

        $this->assertArrayEquals($video, $result);
    }
}
