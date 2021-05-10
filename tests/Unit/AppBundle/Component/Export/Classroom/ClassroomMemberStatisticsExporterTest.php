<?php

namespace Tests\Unit\AppBundle\Component\Export\Classroom;

use AppBundle\Component\Export\Classroom\ClassroomMemberStatisticsExporter;
use Biz\BaseTestCase;

class ClassroomMemberStatisticsExporterTest extends BaseTestCase
{
    public function testGetTitles()
    {
        $this->getContainer()->set('biz', $this->getBiz());
        $exporter = new ClassroomMemberStatisticsExporter($this->getContainer(), []);

        $this->assertEquals([
            'admin.classroom_manage.statistics.member.nickname_th',
            'admin.classroom_manage.statistics.member.phone_number_th',
            'admin.classroom_manage.statistics.member.id_number_th',
            'admin.classroom_manage.statistics.member.create_time_th',
            'admin.classroom_manage.statistics.member.finish_time_th',
            'admin.classroom_manage.statistics.member.learn_time_th',
            'admin.classroom_manage.statistics.member.question_num_th',
            'admin.classroom_manage.statistics.member.note_num_th',
        ], $exporter->getTitles());
    }

    public function testGetContent()
    {
        $classroom = $this->getMockedClassroom();
        $member = $this->getMockedMember();
        $conditions = ['classroomId' => $classroom['id']];
        $this->mockBiz('Classroom:ClassroomService', [
            [
                'functionName' => 'getClassroom',
                'withParams' => [$conditions['classroomId']],
                'returnValue' => $classroom,
            ],
            [
                'functionName' => 'findCoursesByClassroomId',
                'withParams' => [$conditions['classroomId']],
                'returnValue' => [
                    ['id' => 1],
                ],
            ],
            [
                'functionName' => 'findClassroomStudents',
                'withParams' => [$conditions['classroomId'], 0, 1],
                'returnValue' => [$member],
            ],
        ]);
        $this->mockBiz('User:UserService', [
            [
                'functionName' => 'findUsersByIds',
                'withParams' => [[$member['userId']]],
                'returnValue' => [$member['userId'] => ['id' => $member['userId'], 'nickname' => 'test name']],
            ],
            [
                'functionName' => 'findUserProfilesByIds',
                'withParams' => [[$member['userId']]],
                'returnValue' => [$member['userId'] => ['mobile' => $member['mobile']]],
            ],
            [
                'functionName' => 'searchApprovals',
                'withParams' => [['userIds' => [$member['userId']], 'status' => 'approved'], [], 0, 1],
                'returnValue' => [$member['userId'] => ['idcard' => $member['idcard']]],
            ],
        ]);
        $this->mockBiz('Visualization:CoursePlanLearnDataDailyStatisticsService', [
            [
                'functionName' => 'sumLearnedTimeGroupByUserId',
                'withParams' => [['userIds' => [$member['userId']], 'courseIds' => [1]]],
                'returnValue' => [['userId' => $member['userId'], 'learnedTime' => 60]],
            ],
        ]);
        $this->getContainer()->set('biz', $this->getBiz());
        $exporter = new ClassroomMemberStatisticsExporter($this->getContainer(), $conditions);
        $expected = [[
            'test name',
            empty($member['mobile']) ? '--' : $member['mobile']."\t",
            empty($member['idcard']) ? '--' : $member['idcard']."\t",
            date('Y-m-d H:i:s', $member['createdTime']),
            '--',
            1.0,
            $member['questionNum'],
            $member['noteNum'],
        ]];

        $this->assertEquals($expected, $exporter->getContent(0, 1));
    }

    public function testCanExport_returnTrue()
    {
        $exporter = new ClassroomMemberStatisticsExporter($this->getContainer(), []);
        $this->assertTrue($exporter->canExport());
    }

    public function testCanExport_returnFalse()
    {
        $biz = $this->getBiz();
        $user = $biz['user'];
        $user->setPermissions([]);

        $exporter = new ClassroomMemberStatisticsExporter($this->getContainer(), []);
        $this->assertFalse($exporter->canExport());
    }

    public function testGetCount()
    {
        $conditions = ['classroomId' => 1];
        $this->mockBiz('Classroom:ClassroomService', [
            [
                'functionName' => 'getClassroomStudentCount',
                'withParams' => [$conditions['classroomId']],
                'returnValue' => 10,
            ],
        ]);

        $this->getContainer()->set('biz', $this->getBiz());
        $exporter = new ClassroomMemberStatisticsExporter($this->getContainer(), $conditions);
        $this->assertEquals(10, $exporter->getCount());
    }

    public function testBuildConditions()
    {
        $conditions = ['classroomId' => 1];
        $exporter = new ClassroomMemberStatisticsExporter($this->getContainer(), $conditions);

        $this->assertEquals($conditions, $exporter->buildCondition($conditions));
    }

    private function getMockedClassroom(array $classroom = [])
    {
        return array_merge([
            'id' => 1,
            'title' => 'test title',
            'courseNum' => '2',
            'compulsoryTaskNum' => '3',
            'electiveTaskNum' => '1',
            'studentNum' => '2',
            'finishedMemberCount' => '1',
            'income' => '0.01',
            'createdTime' => time(),
            'creator' => $this->getCurrentUser()->getId(),
        ], $classroom);
    }

    private function getMockedMember(array $task = [])
    {
        return array_merge([
            'userId' => $this->getCurrentUser()->getId(),
            'mobile' => '',
            'idcard' => '',
            'createdTime' => time(),
            'finishedTime' => 0,
            'questionNum' => '2',
            'noteNum' => '1',
        ], $task);
    }
}
