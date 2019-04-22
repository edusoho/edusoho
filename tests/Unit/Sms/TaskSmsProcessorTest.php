<?php

namespace Tests\Unit\Sms;

use Biz\BaseTestCase;
use Biz\Sms\SmsProcessor\TaskSmsProcessor;
use AppBundle\Common\ReflectionUtils;
use AppBundle\Common\SmsToolkit;

class TaskSmsProcessorTest extends BaseTestCase
{
    public function testGetUrls()
    {
        $this->mockBiz(
            'Task:TaskService',
            array(
                array(
                    'functionName' => 'getTask',
                    'returnValue' => array(
                        'id' => 1,
                        'courseId' => 1,
                    ),
                    'withParams' => array(1),
                ),
            )
        );
        $this->mockBiz(
            'Course:CourseService',
            array(
                array(
                    'functionName' => 'getCourse',
                    'returnValue' => array(
                        'id' => 1,
                        'parentId' => 1,
                        'locked' => 1,
                    ),
                    'withParams' => array(1),
                ),
            )
        );
        $this->mockBiz(
            'Classroom:ClassroomService',
            array(
                array(
                    'functionName' => 'getClassroomByCourseId',
                    'returnValue' => array(
                        'id' => 1,
                    ),
                    'withParams' => array(1),
                ),
                array(
                    'functionName' => 'searchMemberCount',
                    'returnValue' => 2,
                    'withParams' => array(
                        array(
                            'classroomId' => 1,
                            'role' => 'student',
                        ),
                    ),
                ),
            )
        );

        $this->getSettingService()->set('site', array('url' => 'http://www.edusoho.com'));
        global $kernel;
        $kernel = self::$appKernel;
        $taskSmsProcessor = new TaskSmsProcessor($this->getBiz());
        $expect = $taskSmsProcessor->getUrls(1, 'sms_vip_buy_notify');

        $this->assertArrayEquals(array(
            'count' => 2,
            'urls' => array(
                'http://www.edusoho.com/edu_cloud/sms/callback/task/1?index=0&smsType=sms_vip_buy_notify&sign=lpMWogDZCQMkc760rJINAyng32I8EPsF0SGGjPM%2Fy5M%3D',
            ),
        ), $expect);
    }

    public function testGetSmsInfo()
    {
        $this->mockBiz(
            'Task:TaskService',
            array(
                array(
                    'functionName' => 'getTask',
                    'returnValue' => array(
                        'id' => 1,
                        'courseId' => 2,
                        'fromCourseSetId' => 2,
                        'title' => 'my task title',
                        'startTime' => time(),
                        'type' => 'video',
                    ),
                    'withParams' => array(1),
                ),
            )
        );
        $this->mockBiz(
            'Course:CourseSetService',
            array(
                array(
                    'functionName' => 'getCourseSet',
                    'returnValue' => array(
                        'id' => 2,
                        'parentId' => 1,
                        'title' => 'default courseSet title',
                    ),
                    'withParams' => array(2),
                ),
            )
        );
        $this->mockBiz(
            'Classroom:ClassroomService',
            array(
                array(
                    'functionName' => 'getClassroomByCourseId',
                    'returnValue' => array(
                        'id' => 1,
                    ),
                    'withParams' => array(2),
                ),
                array(
                    'functionName' => 'searchMembers',
                    'returnValue' => array(
                        array('userId' => 1),
                        array('userId' => 2),
                    ),
                    'withParams' => array(
                        array(
                            'classroomId' => 1,
                            'role' => 'student',
                        ),
                        array('createdTime' => 'Desc'),
                        0,
                        1000,
                    ),
                ),
            )
        );
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'findUnlockedUserMobilesByUserIds',
                    'returnValue' => array(
                        '18435180001',
                        '18435180002',
                    ),
                    'withParams' => array(array(1, 2)),
                ),
            )
        );
        $this->mockBiz(
            'System:LogService',
            array(
                array(
                    'functionName' => 'info',
                ),
            )
        );

        $this->getSettingService()->set('site', array('url' => 'http://www.edusoho.com'));
        global $kernel;
        $kernel = self::$appKernel;
        $mockedRequest = $this->mockBiz(
            'Common:SmsToolkit',
            array(
                array(
                    'functionName' => 'getShortLink',
                    'returnValue' => 'http://baidu.com/32Ba',
                    'withParams' => array(
                        'http://www.edusoho.com/course/2/task/1/show',
                        array(),
                    ),
                ),
            )
        );
        ReflectionUtils::setStaticProperty(new SmsToolkit(), 'mockedRequest', $mockedRequest);
        $taskSmsProcessor = new TaskSmsProcessor($this->getBiz());
        $result = $taskSmsProcessor->getSmsInfo(1, 0, 'sms_vip_buy_notify');
        $expect = array(
            'mobile' => '18435180001,18435180002',
            'category' => 'sms_vip_buy_notify',
            'sendStyle' => 'templateId',
            'description' => '课程：《default courseS…itle》 学习任务：《my task title》预告',
            'parameters' => array(
                'lesson_title' => '学习任务：《my task title》',
                'course_title' => '课程：《default courseS…itle》',
                'url' => 'http://baidu.com/32Ba ',
            ),
        );
        $this->assertArrayEquals($expect, $result);
    }

    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }
}
