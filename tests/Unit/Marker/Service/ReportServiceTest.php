<?php

namespace Tests\Unit\Marker\Service;

use Biz\BaseTestCase;

class ReportServiceTest extends BaseTestCase
{
    public function testAnalysisTaskQuestionMarkerWithChoice()
    {
        $expectedQuestionMarker = [
            'questionId' => 1,
            'type' => 'single_choice',
            'stem' => '当过中国总理的是谁?',
            'answer' => [3],
            'metas' => ['choices' => [
                '周星驰', '周杰伦', '周润发', '周恩来',
            ]],
        ];
        $expectedResults = [
            ['answer' => ['B']],
            ['answer' => ['C']],
            ['answer' => ['D']],
            ['answer' => ['A']],
            ['answer' => ['B']],
        ];
        $this->mockBiz(
            'ItemBank:Item:ItemService',
            [
                [
                    'functionName' => 'getItemWithQuestions',
                    'returnValue' => ['id' => 1, 'type' => 'single_choice', 'questions' => [[
                        'id' => 1,
                        'response_points' => [
                            [
                                'radio' => [
                                    'val' => 'A',
                                    'text' => '选项A',
                                ],
                            ],
                            [
                                'radio' => [
                                    'val' => 'B',
                                    'text' => '选项B',
                                ],
                            ],
                            [
                                'radio' => [
                                    'val' => 'C',
                                    'text' => '选项C',
                                ],
                            ],
                            [
                                'radio' => [
                                    'val' => 'D',
                                    'text' => '选项D',
                                ],
                            ],
                        ],
                    ]]],
                ],
            ]
        );
        $this->mockCourseAndTaskService();
        $this->mockQuestionMarkerService($expectedQuestionMarker);
        $this->mockQuestionMarkerResultService($expectedResults);
        $result = $this->getReportService()->analysisQuestionMarker(1, 1, 1);

        $this->assertEquals($expectedQuestionMarker['questionId'], $result['item']['id']);
        $this->assertEquals(5, $result['count']);
        $this->assertEquals(1, $result['metaStats']['A']['answerNum']);
        $this->assertEquals(2, $result['metaStats']['B']['answerNum']);
        $this->assertEquals(100, array_reduce($result['metaStats'], function ($value, $item) {
            $value += $item['pct'];

            return $value;
        }));
    }

    public function testAnalysisTaskQuestionMarkerWithFill()
    {
        $expectedQuestionMarker = [
            'questionId' => 1,
            'type' => 'fill',
            'stem' => '飞碟帽三部曲中第一部是___,说出其中的演员有___.',
            'answer' => [['三国之见龙卸甲'], ['刘德华', '李美琪', '洪金宝']],
        ];
        $expectedResults = [
            ['answer' => ['鸿门宴传奇', '甄子丹']],
            ['answer' => ['锦衣卫', '洪金宝']],
            ['answer' => ['三国之见龙卸甲', '刘德华']],
            ['answer' => ['三国之见龙卸甲', '刘德华']],
            ['answer' => ['三国之见龙卸甲', '李美琪']],
        ];
        $this->mockBiz(
            'ItemBank:Item:ItemService',
            [
                [
                    'functionName' => 'getItemWithQuestions',
                    'returnValue' => ['id' => 1, 'type' => 'fill', 'questions' => [[
                        'id' => 1,
                        'response_points' => [
                            [
                                'text' => [
                                ],
                            ],
                            [
                                'text' => [
                                ],
                            ],
                        ],
                        'answer' => ['三国之见龙卸甲', '刘德华'],
                    ]]],
                ],
            ]
        );
        $this->mockCourseAndTaskService();
        $this->mockQuestionMarkerService($expectedQuestionMarker);
        $this->mockQuestionMarkerResultService($expectedResults);
        $result = $this->getReportService()->analysisQuestionMarker(1, 1, 1);

        $this->assertEquals($expectedQuestionMarker['questionId'], $result['item']['id']);
        $this->assertEquals(5, $result['count']);
        $this->assertEquals(3, $result['metaStats'][0]['answerNum']);
        $this->assertEquals(2, $result['metaStats'][1]['answerNum']);
        $this->assertEquals(40.0, $result['metaStats'][1]['pct']);
    }

    public function testAnalysisTaskQuestionMarkerWithDetermine()
    {
        $expectedQuestionMarker = [
            'questionId' => 1,
            'type' => 'determine',
            'stem' => '张艺谋属于第四代导演',
            'answer' => [0],
        ];
        $expectedResults = [
            ['answer' => ['T']],
            ['answer' => ['F']],
            ['answer' => ['F']],
            ['answer' => ['F']],
            ['answer' => ['T']],
        ];
        $this->mockBiz(
            'ItemBank:Item:ItemService',
            [
                [
                    'functionName' => 'getItemWithQuestions',
                    'returnValue' => ['id' => 1, 'type' => 'fill', 'questions' => [[
                        'id' => 1,
                        'response_points' => [
                            [
                                'radio' => [
                                    'val' => 'T',
                                    'text' => '正确',
                                ],
                            ],
                            [
                                'radio' => [
                                    'val' => 'F',
                                    'text' => '错误',
                                ],
                            ],
                        ],
                        'answer' => ['T'],
                    ]]],
                ],
            ]
        );
        $this->mockCourseAndTaskService();
        $this->mockQuestionMarkerService($expectedQuestionMarker);
        $this->mockQuestionMarkerResultService($expectedResults);
        $result = $this->getReportService()->analysisQuestionMarker(1, 1, 1);

        $this->assertEquals($expectedQuestionMarker['questionId'], $result['item']['id']);
        $this->assertEquals(5, $result['count']);
        $this->assertEquals(2, $result['metaStats'][0]['answerNum']);
    }

    /**
     * @expectedException \Biz\Course\CourseException
     */
    public function testStatLessonQuestionMarkerWithError()
    {
        $this->getReportService()->statTaskQuestionMarker(1002, 1003);
    }

    public function testStatLessonQuestionMarkerSuccess()
    {
        $this->mockBiz(
            'ItemBank:Item:ItemService',
            [
                [
                    'functionName' => 'getItemWithQuestions',
                    'returnValue' => ['id' => 1, 'type' => 'single_choice', 'questions' => [[
                        'id' => 1,
                    ]]],
                ],
                [
                    'functionName' => 'findItemsByIds',
                    'returnValue' => ['1' => [
                        'id' => 1,
                        'type' => 'single_choice',
                        'questions' => [['id' => 1, 'stem' => 'test']],
                    ]],
                ],
            ]
        );
        $this->mockCourseAndTaskService();
        $this->mockMarkerService();
        $this->mockQuestionMarkerService();
        $this->mockQuestionMarkerResultDao();
        $stats = $this->getReportService()->statTaskQuestionMarker(1, 1);

        $this->assertEquals($stats['courseId'], 1);
        $this->assertEquals($stats['taskId'], 1);
        $this->assertEquals($stats['totalUserNum'], 5);
        $this->assertEquals($stats['totalAnswerNum'], 60);
        $this->assertEquals(count($stats['questionMarkers']), 3);
        $this->assertEquals($stats['questionMarkers'][0]['userNum'], 3);
        $this->assertEquals($stats['questionMarkers'][0]['answerNum'], 20);
        $this->assertEquals($stats['questionMarkers'][0]['rightNum'], 20);
        $this->assertEquals($stats['questionMarkers'][0]['markTime'], 20);
        $this->assertEquals($stats['questionMarkers'][0]['pct'], 100);
    }

    private function mockCourseAndTaskService()
    {
        $tasks = [
            ['id' => 1, 'title' => '课时1'],
            ['id' => 2, 'title' => '课时2'],
            ['id' => 3, 'title' => '课时3'],
        ];
        $this->mockBiz('Course:CourseService', [
            ['functionName' => 'getCourse', 'runTimes' => 1, 'returnValue' => ['id' => 1, 'title' => '模拟课程']],
        ]);

        $this->mockBiz('Task:TaskService', [
            ['functionName' => 'getTask', 'runTimes' => 2, 'returnValue' => ['id' => 1, 'title' => '测试课时', 'activityId' => 1]],
            ['functionName' => 'searchTasks', 'runTimes' => 1, 'returnValue' => $tasks],
        ]);

        $this->mockBiz('Activity:ActivityService', [
            ['functionName' => 'getActivity', 'runTimes' => 1, 'returnValue' => ['id' => 1, 'ext' => ['mediaId' => 1]]],
        ]);
    }

    private function mockMarkerService()
    {
        $markers = [
            ['id' => 1, 'mediaId' => 1, 'second' => 20],
        ];
        $this->mockBiz('Marker:MarkerService', [
            ['functionName' => 'findMarkersByMediaId', 'runTimes' => 1, 'returnValue' => $markers],
        ]);
    }

    private function mockQuestionMarkerService($expectedQMarker = [])
    {
        $questionMarkers = [
            ['id' => 1, 'lessonId' => 1, 'markerId' => 1, 'questionId' => 1],
            ['id' => 2, 'lessonId' => 2, 'markerId' => 1, 'questionId' => 1],
            ['id' => 3, 'lessonId' => 3, 'markerId' => 1, 'questionId' => 1],
        ];
        $this->mockBiz('Marker:QuestionMarkerService', [
            ['functionName' => 'findQuestionMarkersByMarkerIds', 'runTimes' => 1, 'returnValue' => $questionMarkers],
            ['functionName' => 'getQuestionMarker', 'runTimes' => 1, 'returnValue' => $expectedQMarker],
        ]);
    }

    private function mockQuestionMarkerResultDao()
    {
        $this->mockBiz('Marker:QuestionMarkerResultDao', [
            ['functionName' => 'countDistinctUserIdByQuestionMarkerIdAndTaskId', 'runTimes' => 3, 'returnValue' => 3],
            ['functionName' => 'count', 'runTimes' => 3, 'returnValue' => 20],
            ['functionName' => 'countDistinctUserIdByTaskId', 'runTimes' => 1, 'returnValue' => 5],
        ]);
    }

    private function mockQuestionMarkerResultService($expectedResults)
    {
        $this->mockBiz('Marker:QuestionMarkerResultService', [
            ['functionName' => 'findByTaskIdAndQuestionMarkerId', 'runTimes' => 1, 'returnValue' => $expectedResults],
        ]);
    }

    protected function getReportService()
    {
        return $this->createService('Marker:ReportService');
    }
}
