<?php

namespace Tests\Unit\AppBundle\Component\Export\Course;

use AppBundle\Component\Export\Course\LiveStatisticsVisitorListExporter;
use Biz\BaseTestCase;
use Biz\User\Service\UserService;

class LiveStatisticsVisitorListExporterTest extends BaseTestCase
{
    public function testBuildCondition()
    {
        list($exporter, $expected) = $this->initExporter();
        $result = $exporter->buildCondition($expected);

        $this->assertEquals($expected, $result);
    }

    public function testGetTitles()
    {
        list($exporter, $conditions) = $this->initExporter();
        $expected = [
            'user.fields.username_label',
            'user.fields.mobile_label',
            'user.fields.email_label',
            'course.live_statistics.first_join',
            'course.live_statistics.last_leave',
            'course.live_statistics.learn_time',
        ];

        $this->assertEquals($expected, $exporter->getTitles());
    }

    public function testCanExport_whenThrowException_thenReturnFalse()
    {
        list($exporter, $conditions) = $this->initExporter();
        $this->assertFalse($exporter->canExport());
    }

    public function testCanExport_whenIsAdmin_thenReturnTrue()
    {
        $this->mockBiz('Course:CourseService', [
            [
                'functionName' => 'tryManageCourse',
                'withParams' => [2],
                'returnValue' => [],
            ],
        ]);

        list($exporter, $conditions) = $this->initExporter();

        $this->assertTrue($exporter->canExport());
    }

    public function testCanExport_whenIsAdminAndNotEmptyCourse_thenReturnTrue()
    {
        $this->mockBiz('Course:CourseService', [
            [
                'functionName' => 'tryManageCourse',
                'withParams' => [2],
                'returnValue' => ['id' => 1],
            ],
        ]);

        list($exporter, $conditions) = $this->initExporter();

        $this->assertTrue($exporter->canExport());
    }

    public function testGetCount_returnZero()
    {
        $this->mockBiz('Live:LiveStatisticsService', [
            [
                'functionName' => 'getVisitorStatisticsByLiveId',
                'returnValue' => ['data' => ['detail' => []]],
            ],
        ]);

        list($exporter, $conditions) = $this->initExporter();
        $this->assertEquals(0, $exporter->getCount());
    }

    public function testGetCount()
    {
        $this->mockBiz('Live:LiveStatisticsService', [
            [
                'functionName' => 'getVisitorStatisticsByLiveId',
                'returnValue' => ['data' => ['detail' => [1, 2, 3]]],
            ],
        ]);

        list($exporter, $conditions) = $this->initExporter();

        $this->assertEquals(3, $exporter->getCount());
    }

    public function testGetContent()
    {
        $this->createUser();
        $this->mockBiz('Live:LiveStatisticsService', [
            [
                'functionName' => 'getVisitorStatisticsByLiveId',
                'returnValue' => [
                    'data' => [
                        'detail' => [
                            [
                                'nickname' => 'testName1',
                                'userId' => 1,
                                'firstJoin' => 1593771295,
                                'lastLeave' => 1593771389,
                                'learnTime' => 94,
                            ],
                            [
                                'nickname' => 'testName2',
                                'userId' => 2,
                                'firstJoin' => 1593771295,
                                'lastLeave' => 1593771319,
                                'learnTime' => 24,
                            ],
                            [
                                'nickname' => 'testName3',
                                'userId' => 3,
                                'firstJoin' => 1593771319,
                                'lastLeave' => 1593771389,
                                'learnTime' => 70,
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        list($exporter, $conditions) = $this->initExporter();

        $this->assertEquals([
            [
                'testName2',
                '13967340620',
                'test_email1@edusoho.com',
                date('Y-m-d H:i:s', 1593771295),
                date('Y-m-d H:i:s', 1593771319),
                ceil(24 / 60),
            ],
            [
                'testName3',
                '13967340622',
                'test_email2@edusoho.com',
                date('Y-m-d H:i:s', 1593771319),
                date('Y-m-d H:i:s', 1593771389),
                ceil(70 / 60), ],
        ], $exporter->getContent(1, 2));
    }

    protected function createUser()
    {
        $userInfo = [
            'id' => 1,
            'nickname' => 'testName1',
            'password' => 'test_password',
            'email' => 'test_email1@edusoho.com',
            'verifiedMobile' => '13967340620',
            'mobile' => '13967340620',
        ];
        $this->getUserService()->register($userInfo);

        $userInfo = [
            'id' => 2,
            'nickname' => 'testName2',
            'password' => 'test_password',
            'email' => 'test_email2@edusoho.com',
            'verifiedMobile' => '13967340622',
            'mobile' => '13967340622',
        ];
        $this->getUserService()->register($userInfo);

        $userInfo = [
            'id' => 3,
            'nickname' => 'testName3',
            'password' => 'test_password',
            'email' => 'test_email3@edusoho.com',
            'verifiedMobile' => '13967340624',
            'mobile' => '13967340624',
        ];
        $this->getUserService()->register($userInfo);
    }

    protected function initExporter($conditions = [])
    {
        $conditions = array_merge([
            'liveId' => 1,
            'courseId' => 2,
            'taskId' => 3,
        ], $conditions);

        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $exporter = new LiveStatisticsVisitorListExporter(self::$appKernel->getContainer(), $conditions);

        return [$exporter, $conditions];
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
