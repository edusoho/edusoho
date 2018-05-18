<?php

namespace Tests\Unit\Sms\SmsProcessor;

use AppBundle\Common\ReflectionUtils;
use AppBundle\Common\SmsToolkit;
use Biz\BaseTestCase;
use Biz\Sms\SmsProcessor\LiveOpenLessonSmsProcessor;
use Biz\System\Service\SettingService;

class LiveOpenLessonSmsProcessorTest extends BaseTestCase
{
    public function testGetUrls()
    {
        $this->mockBiz(
            'OpenCourse:OpenCourseService',
            array(
                array(
                    'functionName' => 'getLesson',
                    'withParams' => array(1),
                    'returnValue' => array(
                        'id' => 1,
                        'courseId' => 1,
                    ),
                ),
                array(
                    'functionName' => 'getCourse',
                    'withParams' => array(1),
                    'returnValue' => array(
                        'id' => 1,
                        'courseId' => 1,
                    ),
                ),
                array(
                    'functionName' => 'countMembers',
                    'withParams' => array(array('courseId' => 1)),
                    'returnValue' => 1,
                ),
            )
        );

        $this->getSettingService()->set('site', array('url' => 'http://www.edusoho.com'));

        $processor = new LiveOpenLessonSmsProcessor($this->getBiz());

        $result = $processor->getUrls(1, 'course');
        $this->assertEquals(1, $result['count']);
        $this->assertContains('http://www.edusoho.com/edu_cloud/sms/callback/liveOpenLesson/1?index=0&smsType=course&sign=8SXJQGtEnSSw033yDDnJisYSqPTIrmvRIc7gVTshkoU%3D', $result['urls']);
    }

    public function testGetSmsInfo()
    {
        $this->mockBiz(
            'OpenCourse:OpenCourseService',
            array(
                array(
                    'functionName' => 'getLesson',
                    'withParams' => array(1),
                    'returnValue' => array(
                        'id' => 1,
                        'courseId' => 1,
                        'type' => 'liveOpen',
                        'startTime' => time() + 86400,
                    ),
                ),
                array(
                    'functionName' => 'getCourse',
                    'withParams' => array(1),
                    'returnValue' => array(
                        'id' => 1,
                        'courseId' => 1,
                        'title' => 'test course',
                    ),
                ),
                array(
                    'functionName' => 'searchMembers',
                    'withParams' => array(array('courseId' => 1), array('createdTime' => 'Desc'), 0, 1000),
                    'returnValue' => array(
                        array('id' => 1, 'mobile' => 11111111111),
                    ),
                ),
            )
        );

        $this->getSettingService()->set('site', array('url' => 'http://www.edusoho.com'));

        $processor = new LiveOpenLessonSmsProcessor($this->getBiz());

        $mockedRequest = $this->mockBiz(
            'Common:SmsToolkit',
            array(
                array(
                    'functionName' => 'getShortLink',
                    'returnValue' => 'http://baidu.com/32Ba',
                    'withParams' => array(
                        'http://www.edusoho.com/open/course/1',
                        array(),
                    ),
                ),
            )
        );
        ReflectionUtils::setStaticProperty(new SmsToolkit(), 'mockedRequest', $mockedRequest);

        $result = $processor->getSmsInfo(1, 0, 'course');
        $this->assertEquals('11111111111', $result['mobile']);
        $this->assertEquals('http://baidu.com/32Ba ', $result['parameters']['url']);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetSmsInfoWithException()
    {
        $this->mockBiz(
            'OpenCourse:OpenCourseService',
            array(
                array(
                    'functionName' => 'getLesson',
                    'withParams' => array(1),
                    'returnValue' => null,
                ),
            )
        );

        $processor = new LiveOpenLessonSmsProcessor($this->getBiz());

        $processor->getSmsInfo(1, 0, 'course');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }
}
