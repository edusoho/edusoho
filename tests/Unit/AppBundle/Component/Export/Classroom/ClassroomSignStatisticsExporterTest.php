<?php

namespace Tests\Unit\AppBundle\Component\Export\Classroom;

use AppBundle\Component\Export\Classroom\ClassroomSignStatisticsExporter;
use Biz\BaseTestCase;

class ClassroomSignStatisticsExporterTest extends BaseTestCase
{
    public function testGetTitles()
    {
        $this->getContainer()->set('biz', $this->getBiz());
        $exporter = new ClassroomSignStatisticsExporter($this->getContainer(), []);

        $this->assertEquals([
            'classroom.manage.sign_statictics.nickname_th',
            'classroom.manage.sign_statictics.role_th',
            'classroom.manage.sign_statictics.join_time_th',
            'classroom.manage.sign_statictics.sign_days_th',
            'classroom.manage.sign_statictics.keep_days_th',
        ], $exporter->getTitles());
    }

    public function testGetContent()
    {
        $members = [
            ['userId' => '1', 'role' => ['student', 'teacher', 'headTeacher'], 'createdTime' => strtotime('-3 day'), 'signDays' => '3', 'keepDays' => '1'],
            ['userId' => '2', 'role' => ['auditor'], 'createdTime' => strtotime('-4 day'), 'signDays' => '3', 'keepDays' => '1'],
            ['userId' => '3', 'role' => ['student', 'teacher'], 'createdTime' => strtotime('-4 day'), 'signDays' => '3', 'keepDays' => '1'],
            ['userId' => '4', 'role' => ['student', 'assistant'], 'createdTime' => strtotime('-4 day'), 'signDays' => '3', 'keepDays' => '1'],
            ['userId' => '5', 'role' => ['student'], 'createdTime' => strtotime('-4 day'), 'signDays' => '3', 'keepDays' => '1'],
        ];

        $this->mockBiz('Classroom:ClassroomService', [
            [
                'functionName' => 'searchMembersSignStatistics',
                'returnValue' => $members,
            ],
        ]);
        $this->mockBiz('User:UserService', [
            [
                'functionName' => 'findUsersByIds',
                'returnValue' => [
                    1 => ['nickname' => 'nickname1'],
                    2 => ['nickname' => 'nickname2'],
                    3 => ['nickname' => 'nickname3'],
                    4 => ['nickname' => 'nickname4'],
                    5 => ['nickname' => 'nickname5'],
                ],
            ],
        ]);

        $this->getContainer()->set('biz', $this->getBiz());
        $expected = [
            ['nickname1', '班主任', date('Y-m-d H:i:s', $members[0]['createdTime']), $members[0]['signDays'], $members[0]['keepDays']],
            ['nickname2', '旁听生', date('Y-m-d H:i:s', $members[1]['createdTime']), $members[1]['signDays'], $members[1]['keepDays']],
            ['nickname3', '老师', date('Y-m-d H:i:s', $members[2]['createdTime']), $members[2]['signDays'], $members[2]['keepDays']],
            ['nickname4', '助教', date('Y-m-d H:i:s', $members[3]['createdTime']), $members[3]['signDays'], $members[3]['keepDays']],
            ['nickname5', '正式学员', date('Y-m-d H:i:s', $members[4]['createdTime']), $members[4]['signDays'], $members[4]['keepDays']],
        ];
        $exporter = new ClassroomSignStatisticsExporter($this->getContainer(), ['classroomId' => '1']);

        $this->assertEquals($expected, $exporter->getContent(0, 1));
    }

    public function testCanExport_returnTrue()
    {
        $this->mockBiz('Classroom:ClassroomService', [
            [
                'functionName' => 'canManageClassroom',
                'returnValue' => true,
            ],
        ]);
        $this->getContainer()->set('biz', $this->getBiz());

        $exporter = new ClassroomSignStatisticsExporter($this->getContainer(), ['classroomId' => '1']);
        $this->assertTrue($exporter->canExport());
    }

    public function testCanExport_returnFalse()
    {
        $exporter = new ClassroomSignStatisticsExporter($this->getContainer(), []);
        $this->assertFalse($exporter->canExport());

        $this->mockBiz('Classroom:ClassroomService', [
            [
                'functionName' => 'canManageClassroom',
                'returnValue' => false,
            ],
        ]);
        $this->getContainer()->set('biz', $this->getBiz());
        $exporter = new ClassroomSignStatisticsExporter($this->getContainer(), ['classroomId' => '1']);
        $this->assertFalse($exporter->canExport());
    }

    public function testGetCount()
    {
        $conditions = ['classroomId' => 1];
        $this->mockBiz('Classroom:ClassroomService', [
            [
                'functionName' => 'searchMemberCount',
                'withParams' => [$conditions],
                'returnValue' => 200,
            ],
        ]);

        $this->getContainer()->set('biz', $this->getBiz());
        $exporter = new ClassroomSignStatisticsExporter($this->getContainer(), $conditions);
        $this->assertEquals(200, $exporter->getCount());
    }

    public function testBuildConditions()
    {
        $conditions = ['classroomId' => 1];
        $exporter = new ClassroomSignStatisticsExporter($this->getContainer(), $conditions);
        $result = $exporter->buildCondition($conditions);
        $this->assertEquals($conditions, $result);
    }
}
