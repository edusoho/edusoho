<?php

namespace Tests\Unit\AppBundle\Component\Export\Classroom;

use AppBundle\Component\Export\Classroom\ClassroomStatisticsExporter;
use Biz\BaseTestCase;

class ClassroomStatisticsExporterTest extends BaseTestCase
{
    public function testGetTitles()
    {
        $this->getContainer()->set('biz', $this->getBiz());
        $exporter = new ClassroomStatisticsExporter($this->getContainer(), []);

        $this->assertEquals([
            'admin.classroom_manage.statistics.name_th',
            'admin.classroom_manage.statistics.course_number_th',
            'admin.classroom_manage.statistics.task_number_th',
            'admin.classroom_manage.statistics.student_number_th',
            'admin.classroom_manage.statistics.finish_number_th',
            'admin.classroom_manage.statistics.income_th',
            'admin.classroom_manage.statistics.create_time_th',
            'admin.classroom_manage.statistics.creator_th',
        ], $exporter->getTitles());
    }

    public function testGetContent()
    {
        $time = time();
        $classroom = [
            'id' => 1,
            'title' => 'test title',
            'courseNum' => '2',
            'compulsoryTaskNum' => '3',
            'electiveTaskNum' => '1',
            'studentNum' => '2',
            'finishedMemberCount' => '1',
            'income' => '0.01',
            'createdTime' => $time,
            'creator' => $this->getCurrentUser()->getId(),
        ];
        $conditions = ['classroomId' => $classroom['id']];
        $this->mockBiz('Classroom:ClassroomService', [
            [
                'functionName' => 'searchClassroomsWithStatistics',
                'withParams' => [$conditions, ['createdTime' => 'desc'], 0, 1],
                'returnValue' => [$classroom],
            ],
        ]);
        $this->mockBiz('User:UserService', [
            [
                'functionName' => 'findUsersByIds',
                'returnValue' => [
                    [
                        'id' => $this->getCurrentUser()->getId(),
                        'nickname' => $this->getCurrentUser()->getUsername(),
                    ],
                ],
            ],
        ]);

        $this->getContainer()->set('biz', $this->getBiz());
        $exporter = new ClassroomStatisticsExporter($this->getContainer(), $conditions);
        $expected = [[
            $classroom['title'],
            $classroom['courseNum'],
            $classroom['compulsoryTaskNum'].'('.$classroom['electiveTaskNum'].')',
            $classroom['studentNum'],
            $classroom['finishedMemberCount'],
            $classroom['income'],
            date('Y-m-d H:i:s', $classroom['createdTime']),
            $this->getCurrentUser()->getUsername(),
        ]];
        $this->assertEquals($expected, $exporter->getContent(0, 1));
    }

    public function testCanExport_returnTrue()
    {
        $exporter = new ClassroomStatisticsExporter($this->getContainer(), []);
        $this->assertTrue($exporter->canExport());
    }

    public function testCanExport_returnFalse()
    {
        $biz = $this->getBiz();
        $user = $biz['user'];
        $user->setPermissions([]);

        $exporter = new ClassroomStatisticsExporter($this->getContainer(), []);
        $this->assertFalse($exporter->canExport());
    }

    public function testGetCount()
    {
        $conditions = ['classroomId' => 1];
        $this->mockBiz('Classroom:ClassroomService', [
            [
                'functionName' => 'countClassrooms',
                'withParams' => [$conditions],
                'returnValue' => 200,
            ],
        ]);

        $this->getContainer()->set('biz', $this->getBiz());
        $exporter = new ClassroomStatisticsExporter($this->getContainer(), $conditions);
        $this->assertEquals(200, $exporter->getCount());
    }

    public function testBuildConditions_withOrgUnabled()
    {
        $conditions = ['classroomId' => 1, 'orgCode' => '1.'];
        $exporter = new ClassroomStatisticsExporter($this->getContainer(), $conditions);
        $result = $exporter->buildCondition($conditions);
        $this->assertEquals(['classroomId' => 1], $result);
    }

    public function testBuildConditions_withOrgEnabled()
    {
        $conditions = ['classroomId' => 1, 'orgCode' => '2.'];
        $this->mockBiz('System:SettingService', [
            [
                'functionName' => 'node',
                'withParams' => ['magic.enable_org'],
                'returnValue' => true,
            ],
        ]);

        $this->getContainer()->set('biz', $this->getBiz());
        $exporter = new ClassroomStatisticsExporter($this->getContainer(), $conditions);
        $result = $exporter->buildCondition($conditions);
        $this->assertEquals(['classroomId' => 1, 'likeOrgCode' => '2.'], $result);
    }

    public function testBuildConditions_withOrgEnabledAndEmptyOrgCode()
    {
        $conditions = ['classroomId' => 1];
        $this->mockBiz('System:SettingService', [
            [
                'functionName' => 'node',
                'withParams' => ['magic.enable_org'],
                'returnValue' => true,
            ],
        ]);

        $exporter = new ClassroomStatisticsExporter($this->getContainer(), $conditions);
        $result = $exporter->buildCondition($conditions);
        $this->assertEquals(['classroomId' => 1, 'likeOrgCode' => $this->getCurrentUser()->getSelectOrgCode()], $result);
    }
}
