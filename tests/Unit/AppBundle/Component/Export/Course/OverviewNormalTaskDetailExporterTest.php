<?php

namespace Tests\Unit\Component\Export\Invite;

use Biz\BaseTestCase;
use  AppBundle\Component\Export\Course\OverviewNormalTaskDetailExporter;

class OverviewNormalTaskDetailExporterTest extends BaseTestCase
{
    public function testgetContent()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'findUsersByIds',
                    'returnValue' => array(
                        1 => array(
                            'id' => 1,
                            'nickname' => 'lalala',
                        ),
                    ),
                ),
            )
        );
        $this->mockBiz(
            'Testpaper:TestpaperService',
            array(
                array(
                    'functionName' => 'findTestResultsByTestpaperIdAndUserIds',
                    'returnValue' => array(
                        1 => array(
                            'id' => '1',
                            'userId' => 1,
                            'usedTime' => 41,
                            'firstScore' => 1,
                            'maxScore' => 14,
                        ),
                    ),
                ),
                array(
                    'functionName' => 'getTestpaperByIdAndType',
                    'returnValue' => array(
                        'id' => 1,
                    ),
                ),
            )
        );

        $this->mockBiz(
            'Task:TaskResultService',
            array(
                array(
                    'functionName' => 'searchTaskResults',
                    'returnValue' => array(
                        array(
                            'id' => '1',
                            'userId' => 1,
                            'time' => 12,
                            'finishedTime' => 1341,
                            'watchTime' => 13,
                            'createdTime' => 1,
                        ),
                    ),
                ),
            )
        );
        $expoter = new OverviewNormalTaskDetailExporter(self::$appKernel->getContainer(), array(
            'courseTaskId' => 1,
        ));

        $result = $expoter->getContent(0, 100);

        $this->assertArrayEquals(array(
            'lalala',
            '1970-01-01 08:00:01',
            '1970-01-01 08:22:21',
            '0.2',
            '0.2',
        ), $result[0]);
    }

    public function testBuildCondition()
    {
        $expoter = new OverviewNormalTaskDetailExporter(self::$appKernel->getContainer(), array(
            'courseTaskId' => 1,
        ));
        $result = $expoter->buildCondition(array(
            'courseTaskId' => 1,
            'alal' => '1123',
            'titleLike' => '1123',
        ));

        $this->assertArrayEquals(array(
            'courseTaskId' => 1,
        ), $result);
    }

    public function testBuildParameter()
    {
        $expoter = new OverviewNormalTaskDetailExporter(self::$appKernel->getContainer(), array(
            'courseTaskId' => 1,
        ));
        $result = $expoter->buildParameter(array(
            'courseTaskId' => 1,
        ));

        $this->assertArrayEquals(array(
            'start' => 0,
            'fileName' => '',
            'courseTaskId' => 1,
        ), $result);
    }

    public function testGetTitles()
    {
        $expoter = new OverviewNormalTaskDetailExporter(self::$appKernel->getContainer(), array(
            'courseTaskId' => 1,
        ));

        $title = array(
            'task.learn_data_detail.nickname',
            'task.learn_data_detail.createdTime',
            'task.learn_data_detail.finishedTime',
            'task.learn_data_detail.learnTime',
            'task.learn_data_detail.video_and_audio_learnTime',
        );

        $this->assertArrayEquals($title, $expoter->getTitles());
    }

    public function testGetCount()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockBiz(
            'Course:MemberService',
            array(
                array(
                    'functionName' => 'countMembers',
                    'returnValue' => 51,
                ),
            )
        );
        $expoter = new OverviewNormalTaskDetailExporter(self::$appKernel->getContainer(), array(
            'courseTaskId' => 1,
        ));

        $this->assertEquals(51, $expoter->getCount());
    }

    public function testCanExport()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockBiz(
            'Course:CourseService',
            array(
                array(
                    'functionName' => 'tryManageCourse',
                    'returnValue' => false,
                ),
            )
        );
        $expoter = new OverviewNormalTaskDetailExporter(self::$appKernel->getContainer(), array(
            'courseTaskId' => 1,
        ));

        $result = $expoter->canExport();
        $this->assertTrue($result);

        $biz = $this->getBiz();
        $user = $biz['user'];
        $user->setPermissions(array());
        $result = $expoter->canExport();
        $this->assertNotTrue($result);

        $this->mockBiz(
            'Course:CourseService',
            array(
                array(
                    'functionName' => 'tryManageCourse',
                    'returnValue' => true,
                ),
            )
        );
        $result = $expoter->canExport();
        $this->assertTrue($result);
    }
}
