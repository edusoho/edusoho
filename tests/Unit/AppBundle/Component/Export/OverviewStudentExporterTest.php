<?php

namespace Tests\Unit\Component\Export;

use AppBundle\Component\Export\Course\OverviewStudentExporter;
use Biz\BaseTestCase;

class OverviewStudentExporterTest extends BaseTestCase
{
    public function testGetTitles()
    {
        $biz = $this->getBiz();
        for ($i = 0; $i <= 4; ++$i) {
            $this->getTaskDao()->create(['title' => 'test'.$i, 'type' => 'vedio', 'courseId' => 1, 'isOptional' => 0, 'status' => 'published', 'createdUserId' => 1]);
        }
        $expoter = new OverviewStudentExporter(self::$appKernel->getContainer(), [
            'courseId' => 1,
        ]);

        $title = $expoter->getTitles();
        $result = [
            'task.learn_data_detail.nickname',
            'task.learn_data_detail.finished_rate',
            'test0',
            'test1',
            'test2',
            'test3',
            'test4',
        ];

        $this->assertArrayEquals($result, $title);
    }

    public function testCanExport()
    {
        $expoter = new OverviewStudentExporter(self::$appKernel->getContainer(), [
            'courseId' => 1,
        ]);
        $result = $expoter->canExport();
        $this->assertEquals(false, $result);

        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockBiz(
            'Course:CourseService',
            [
                [
                    'functionName' => 'tryManageCourse',
                    'returnValue' => true,
                    'withParams' => [
                        1,
                    ],
                ],
                [
                    'functionName' => 'getCourse',
                    'returnValue' => ['id' => 1],
                    'withParams' => [
                        1,
                    ],
                ],
            ]
        );
        $expoter = new OverviewStudentExporter(self::$appKernel->getContainer(), [
            'courseId' => 1,
        ]);

        $result = $expoter->canExport();
        $this->assertEquals(true, $result);
    }

    public function testGetCount()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockBiz(
            'Course:ReportService',
            [
                [
                    'functionName' => 'buildStudentDetailConditions',
                    'returnValue' => ['role' => 'student', 'courseId' => 1],
                    'withParams' => [
                      [
                            'courseId' => 1,
                        ],
                        1,
                    ],
                ],
                [
                    'functionName' => 'buildStudentDetailOrderBy',
                    'returnValue' => ['createdTime' => 'desc'],
                    'withParams' => [
                      [
                            'courseId' => 1,
                        ],
                    ],
                ],
            ]
        );
        $this->mockBiz(
            'Course:MemberService',
            [
                [
                    'functionName' => 'countMembers',
                    'returnValue' => 10,
                    'withParams' => [
                    ],
                ],
            ]
        );
        $expoter = new OverviewStudentExporter(self::$appKernel->getContainer(), [
            'courseId' => 1,
        ]);

        $count = $expoter->getCount();
        $this->assertEquals(10, $count);
    }

    public function testBuildParameter()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockBiz(
            'Course:ReportService',
            [
                [
                    'functionName' => 'buildStudentDetailConditions',
                    'returnValue' => ['role' => 'student', 'courseId' => 1],
                    'withParams' => [
                      [
                            'courseId' => 1,
                            'start' => 1,
                        ],
                        1,
                    ],
                ],
                [
                    'functionName' => 'buildStudentDetailOrderBy',
                    'returnValue' => 'createdTime',
                    'withParams' => [
                      [
                            'courseId' => 1,
                            'start' => 1,
                        ],
                    ],
                ],
            ]
        );
        $expoter = new OverviewStudentExporter(self::$appKernel->getContainer(), [
            'courseId' => 1,
            'start' => 1,
        ]);
        $parameter = $expoter->buildParameter([
            'courseId' => 1,
            'start' => 1,
        ]);

        $this->assertEquals(1, $parameter['start']);
        $this->assertEquals('', $parameter['fileName']);
        $this->assertEquals(1, $parameter['courseId']);
        $this->assertEquals('createdTime', $parameter['orderBy']);
    }

    public function testBuildCondition()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockBiz(
            'Course:ReportService',
            [
                [
                    'functionName' => 'buildStudentDetailConditions',
                    'returnValue' => ['role' => 'student', 'courseId' => 1],
                    'withParams' => [
                      [
                            'courseId' => 1,
                            'start' => 1,
                        ],
                        1,
                    ],
                ],
                [
                    'functionName' => 'buildStudentDetailOrderBy',
                    'returnValue' => 'createdTime',
                    'withParams' => [
                      [
                            'courseId' => 1,
                            'start' => 1,
                        ],
                    ],
                ],
            ]
        );
        $expoter = new OverviewStudentExporter(self::$appKernel->getContainer(), [
            'courseId' => 1,
            'start' => 1,
        ]);

        $conditions = $expoter->buildCondition([
            'courseId' => 1,
            'start' => 1,
        ]);

        $this->assertArrayEquals(['role' => 'student', 'courseId' => 1], $conditions);
    }

    public function testGetContent()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockBiz(
            'Course:ReportService',
            [
                [
                    'functionName' => 'buildStudentDetailConditions',
                    'returnValue' => ['role' => 'student', 'courseId' => 1],
                    'withParams' => [
                      [
                            'courseId' => 1,
                            'start' => 1,
                        ],
                        1,
                    ],
                ],
                [
                    'functionName' => 'buildStudentDetailOrderBy',
                    'returnValue' => 'createdTime',
                    'withParams' => [
                      [
                            'courseId' => 1,
                            'start' => 1,
                        ],
                    ],
                ],
                [
                    'functionName' => 'getStudentDetail',
                    'returnValue' => [
                        [
                            1 => [
                                'id' => 1,
                                'nickname' => 'test1',
                            ],
                            2 => [
                                'id' => 2,
                                'nickname' => 'test2',
                            ],
                        ],
                        [
                            3 => [
                                'id' => 3,
                            ],
                            4 => [
                                'id' => 4,
                            ],
                        ],
                        [
                            1 => [
                                 3 => [
                                     'status' => 'start',
                                 ],
                            ],
                            2 => [
                                 4 => [
                                     'status' => 'finish',
                                 ],
                            ],
                        ], ],
                ],
            ]
        );

        $this->mockBiz(
            'Course:MemberService',
            [
                [
                    'functionName' => 'searchMembers',
                    'returnValue' => [
                        ['userId' => 1, 'learnedCompulsoryTaskNum' => 1],
                        ['userId' => 2, 'learnedCompulsoryTaskNum' => 2],
                    ],
                ],
            ]
        );
        $this->mockBiz(
            'Task:TaskService',
            [
                [
                    'functionName' => 'countTasks',
                    'returnValue' => 10,
                ],
            ]
        );
        $this->mockBiz(
            'Course:CourseService',
            [
                [
                    'functionName' => 'tryManageCourse',
                    'returnValue' => true,
                ],
                [
                    'functionName' => 'getCourse',
                    'returnValue' => ['id' => 1, 'compulsoryTaskNum' => 2],
                ],
            ]
        );
        $expoter = new OverviewStudentExporter(self::$appKernel->getContainer(), [
            'courseId' => 1,
            'start' => 1,
        ]);

        $data = $expoter->getContent(0, 10);

        $this->assertArrayEquals(
            [
                'test1'."\t", '50%', '学习中', '未开始',
            ], $data[0]
        );
        $this->assertArrayEquals(
            [
                'test2'."\t", '100%', '未开始', '已完成',
            ], $data[1]
        );
    }

    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    protected function getTaskDao()
    {
        return $this->getBiz()->dao('Task:TaskDao');
    }

    protected function getCourseDao()
    {
        return $this->createDao('Course:CourseDao');
    }
}
