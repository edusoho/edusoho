<?php

namespace Tests\Unit\AppBundle\Component\Export\Classroom;

use AppBundle\Component\Export\Classroom\ClassroomCourseStatisticsExporter;
use Biz\BaseTestCase;

class ClassroomCourseStatisticsExporterTest extends BaseTestCase
{
    public function testGetTitles()
    {
        $this->getContainer()->set('biz', $this->getBiz());
        $exporter = new ClassroomCourseStatisticsExporter($this->getContainer(), []);

        $this->assertEquals([
            'admin.course_manage.statistics.data.name',
            'admin.course_manage.statistics.data.task_type',
            'admin.course_manage.statistics.data.video_length',
            'admin.course_manage.statistics.data.study_number',
            'admin.course_manage.statistics.data.finished_number',
            'admin.course_manage.statistics.data.task_sum_study_time',
            'admin.course_manage.statistics.data.average_study_time',
            'admin.course_manage.statistics.data.average_score',
        ], $exporter->getTitles());
    }

    public function testGetContent_withEmptyCourseId()
    {
        $classroom = $this->getMockedClassroom();
        $task = $this->getMockedTask(['type' => 'video', 'length' => 60]);
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
                    ['id' => 2],
                    ['id' => 1],
                ],
            ],
        ]);
        $this->mockBiz('Task:TaskService', [
            [
                'functionName' => 'searchTasksWithStatistics',
                'withParams' => [['courseId' => 2], ['id' => 'ASC'], 0, 1],
                'returnValue' => [$task],
            ],
        ]);

        $this->getContainer()->set('biz', $this->getBiz());
        $exporter = new ClassroomCourseStatisticsExporter($this->getContainer(), $conditions);
        $expected = [[
            $task['title'],
            $this->getContainer()->get('translator')->trans('course.activity.'.$task['type']),
            '1.0',
            $task['studentNum'],
            $task['finishedNum'],
            $task['sumLearnedTime'],
            $task['avgLearnedTime'],
            '--',
        ]];

        $this->assertEquals($expected, $exporter->getContent(0, 1));
    }

    public function testGetContent()
    {
        $classroom = $this->getMockedClassroom();
        $task = $this->getMockedTask(['type' => 'testpaper', 'score' => 2]);
        $conditions = ['classroomId' => $classroom['id'], 'courseId' => 2];
        $this->mockBiz('Classroom:ClassroomService', [
            [
                'functionName' => 'getClassroom',
                'withParams' => [$conditions['classroomId']],
                'returnValue' => $classroom,
            ],
        ]);
        $this->mockBiz('Task:TaskService', [
            [
                'functionName' => 'searchTasksWithStatistics',
                'withParams' => [['courseId' => 2], ['id' => 'ASC'], 0, 1],
                'returnValue' => [$task],
            ],
        ]);

        $this->getContainer()->set('biz', $this->getBiz());
        $exporter = new ClassroomCourseStatisticsExporter($this->getContainer(), $conditions);
        $expected = [[
            $task['title'],
            $this->getContainer()->get('translator')->trans('course.activity.'.$task['type']),
            '--',
            $task['studentNum'],
            $task['finishedNum'],
            $task['sumLearnedTime'],
            $task['avgLearnedTime'],
            '2',
        ]];

        $this->assertEquals($expected, $exporter->getContent(0, 1));
    }

    public function testCanExport_returnTrue()
    {
        $exporter = new ClassroomCourseStatisticsExporter($this->getContainer(), []);
        $this->assertTrue($exporter->canExport());
    }

    public function testCanExport_returnFalse()
    {
        $biz = $this->getBiz();
        $user = $biz['user'];
        $user->setPermissions([]);

        $exporter = new ClassroomCourseStatisticsExporter($this->getContainer(), []);
        $this->assertFalse($exporter->canExport());
    }

    public function testGetCount()
    {
        $conditions = ['classroomId' => 1, 'courseId' => 1];
        $this->mockBiz('Task:TaskService', [
            [
                'functionName' => 'countTasks',
                'withParams' => [$conditions],
                'returnValue' => 32,
            ],
        ]);

        $this->getContainer()->set('biz', $this->getBiz());
        $exporter = new ClassroomCourseStatisticsExporter($this->getContainer(), $conditions);
        $this->assertEquals(32, $exporter->getCount());
    }

    public function testBuildConditions()
    {
        $conditions = ['classroomId' => 1, 'courseId' => 1];
        $exporter = new ClassroomCourseStatisticsExporter($this->getContainer(), $conditions);

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

    private function getMockedTask(array $task = [])
    {
        return array_merge([
            'title' => 'test task title',
            'type' => 'text',
            'length' => '0',
            'studentNum' => '3',
            'finishedNum' => '1',
            'studentNum' => '2',
            'sumLearnedTime' => '60',
            'avgLearnedTime' => '2',
        ], $task);
    }
}
