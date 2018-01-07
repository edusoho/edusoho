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

    /**
     * @expectedException \AppBundle\Component\MediaParser\ParseException
     */
    public function testParseAlbum()
    {
        $proxy = new ParserProxy();
        $video = $proxy->parseAlbum('http://v.163.com/special/M941471K5_M9414FGNS.html');
    }

    public function testParserNotFoundException()
    {
        $proxy = new ParserProxy();
        $result = ReflectionUtils::invokeMethod($proxy, 'createParserNotFoundException', array('exc'));

        $this->assertEquals('AppBundle\Component\MediaParser\ParserNotFoundException', get_class($result));
        $this->assertEquals('exc', $result->getMessage());
    }
}
