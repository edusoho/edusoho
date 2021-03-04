<?php

namespace Tests\Unit\Component\Export\Classroom;

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
        $this->mockBiz('Classroom:ClassroomService', [
            [
                'functionName' => 'searchClassroomsWithStatistics',
                'withParams' => [[], ['createdTime' => 'desc'], 0, 1],
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
        $exporter = new ClassroomStatisticsExporter($this->getContainer(), []);
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
}
