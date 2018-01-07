<?php

namespace Tests\Unit\Component\Export;

use Biz\BaseTestCase;
use AppBundle\Component\Export\Course\UserLearnStatistics;
use AppBundle\Component\Export\UserLearnStatistics\UserLearnStatisticsExporter;
use AppBundle\Common\ReflectionUtils;

class UserLearnStatisticsExporterTest extends BaseTestCase
{
    public function testGetTitles()
    {
        $expoter = new UserLearnStatisticsExporter(self::$appKernel->getContainer(), array(
        ));

        $result = array('user.learn.statistics.nickname', 'user.learn.statistics.join.classroom.num', 'user.learn.statistics.exit.classroom.num', 'user.learn.statistics.join.course.num', 'user.learn.statistics.exit.course.num', 'user.learn.statistics.finished.task.num', 'user.learn.statistics.learned.econds', 'user.learn.statistics.actual.amount');

        $this->assertArrayEquals($expoter->getTitles(), $result);
    }

    public function testBuildCondition()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'searchUsers',
                    'returnValue' => array(array('id' => 1)),
                )
            )
        );
        $expoter = new UserLearnStatisticsExporter(self::$appKernel->getContainer(), array(
        ));

        $contisions = array();     
        $result = $expoter->buildCondition($contisions);
        $this->assertEquals(array(), $result['userIds']);

        $contisions = array('nickname' => 'la');

        $result = $expoter->buildCondition($contisions);
        $this->assertEquals(1, $result['userIds'][0]);       
        $this->assertNotTrue(isset($result['nickname']));          
    }

    public function testCanExport()
    {
        $expoter = new UserLearnStatisticsExporter(self::$appKernel->getContainer(), array(
        ));
        $this->assertEquals(true, $expoter->canExport());

        $biz = $this->getBiz();
        $user = $biz['user'];
        $user->setPermissions(array());

        $this->assertEquals(false, $expoter->canExport());
    }

    public function testGetCount()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'countUsers',
                    'returnValue' => 3,
                )
            )
        );
        $expoter = new UserLearnStatisticsExporter(self::$appKernel->getContainer(), array(
        ));
        $count = $expoter->getCount();
        
        $this->assertEquals(3, $expoter->getCount());
    }

    public function testHandlerStatistics()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'countUsers',
                    'returnValue' => 3,
                )
            )
        );
        $expoter = new UserLearnStatisticsExporter(self::$appKernel->getContainer(), array(
        ));

        $users = array(
            array(
                'id' => 1,
                'nickname' => 'lalal',
            )

        );
        $statistics = array(
            array(
                'userId' => 1,
                'joinedClassroomNum' => 3,
                'exitClassroomNum'=> 4,
                'joinedCourseNum'=> 35,
                'exitCourseNum'=> 13,
                'finishedTaskNum'=> 23,
                'learnedSeconds' => 60,
                'actualAmount' => 100,
            ),
        );
        $data = ReflectionUtils::invokeMethod($expoter, 'handlerStatistics', array($statistics, $users));
        
        $this->assertArrayEquals(array(
            'lalal',
            '3',
            '4',
            '35',
            '13',
            '23',
            '1',
            '1',
        ), $data[0]);
    }

    public function testGetContent()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $expoter = new UserLearnStatisticsExporter(self::$appKernel->getContainer(), array(
        ));

        $data = $expoter->getContent(0,10);
        $this->assertArrayEquals(array(
            'admin',
            '0',
            '0',
            '0',
            '0',
            '0',
            '0',
            '0',
        ), $data[0]);
    }
}