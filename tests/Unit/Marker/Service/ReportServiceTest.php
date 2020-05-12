<?php

namespace Tests\Unit\Marker\Service;

use Biz\BaseTestCase;

class ReportServiceTest extends BaseTestCase
{
    public function testAnalysisTaskQuestionMarkerWithChoice()
    {
        $expectedQuestionMarker = array(
            'questionId' => 1,
            'type' => 'single_choice',
            'stem' => '当过中国总理的是谁?',
            'answer' => array(3),
            'metas' => array('choices' => array(
                '周星驰', '周杰伦', '周润发', '周恩来',
            )),
        );
        $expectedResults = array(
            array('answer' => array('B')),
            array('answer' => array('C')),
            array('answer' => array('D')),
            array('answer' => array('A')),
            array('answer' => array('B')),
        );
        $this->mockBiz(
            'ItemBank:Item:ItemService',
            array(
                array(
                    'functionName' => 'getItemWithQuestions',
                    'returnValue' => array('id' => 1, 'type' => 'single_choice', 'questions' => array(array(
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
                        ]
                    ))),
                ),
            )
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
        $expectedQuestionMarker = array(
            'questionId' => 1,
            'type' => 'fill',
            'stem' => '飞碟帽三部曲中第一部是___,说出其中的演员有___.',
            'answer' => array(array('三国之见龙卸甲'), array('刘德华', '李美琪', '洪金宝')),
        );
        $expectedResults = array(
            array('answer' => array('鸿门宴传奇', '甄子丹')),
            array('answer' => array('锦衣卫', '洪金宝')),
            array('answer' => array('三国之见龙卸甲', '刘德华')),
            array('answer' => array('三国之见龙卸甲', '刘德华')),
            array('answer' => array('三国之见龙卸甲', '李美琪')),
        );
        $this->mockBiz(
            'ItemBank:Item:ItemService',
            array(
                array(
                    'functionName' => 'getItemWithQuestions',
                    'returnValue' => array('id' => 1, 'type' => 'fill', 'questions' => array(array(
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
                        'answer' => ['三国之见龙卸甲', '刘德华']
                    ))),
                ),
            )
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
        $expectedQuestionMarker = array(
            'questionId' => 1,
            'type' => 'determine',
            'stem' => '张艺谋属于第四代导演',
            'answer' => array(0),
        );
        $expectedResults = array(
            array('answer' => array('T')),
            array('answer' => array('F')),
            array('answer' => array('F')),
            array('answer' => array('F')),
            array('answer' => array('T')),
        );
        $this->mockBiz(
            'ItemBank:Item:ItemService',
            array(
                array(
                    'functionName' => 'getItemWithQuestions',
                    'returnValue' => array('id' => 1, 'type' => 'fill', 'questions' => array(array(
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
                        'answer' => ['T']
                    ))),
                ),
            )
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
            array(
                array(
                    'functionName' => 'getItemWithQuestions',
                    'returnValue' => array('id' => 1, 'type' => 'single_choice', 'questions' => array(array(
                        'id' => 1,
                    ))),
                ),
                array(
                    'functionName' => 'findItemsByIds',
                    'returnValue' => array('1' => array(
                        'id' => 1,
                        'type' => 'single_choice',
                        'questions' => array(array('id' => 1, 'stem' => 'test'))
                    )),
                ),
            )
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
        $tasks = array(
            array('id' => 1, 'title' => '课时1'),
            array('id' => 2, 'title' => '课时2'),
            array('id' => 3, 'title' => '课时3'),
        );
        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'getCourse', 'runTimes' => 1, 'returnValue' => array('id' => 1, 'title' => '模拟课程')),
        ));

        $this->mockBiz('Task:TaskService', array(
            array('functionName' => 'getTask', 'runTimes' => 2, 'returnValue' => array('id' => 1, 'title' => '测试课时', 'activityId' => 1)),
            array('functionName' => 'searchTasks', 'runTimes' => 1, 'returnValue' => $tasks),
        ));

        $this->mockBiz('Activity:ActivityService', array(
            array('functionName' => 'getActivity', 'runTimes' => 1, 'returnValue' => array('id' => 1, 'ext' => array('mediaId' => 1))),
        ));
    }

    private function mockMarkerService()
    {
        $markers = array(
            array('id' => 1, 'mediaId' => 1, 'second' => 20),
        );
        $this->mockBiz('Marker:MarkerService', array(
            array('functionName' => 'findMarkersByMediaId', 'runTimes' => 1, 'returnValue' => $markers),
        ));
    }

    private function mockQuestionMarkerService($expectedQMarker = array())
    {
        $questionMarkers = array(
            array('id' => 1, 'lessonId' => 1, 'markerId' => 1, 'questionId' => 1),
            array('id' => 2, 'lessonId' => 2, 'markerId' => 1, 'questionId' => 1),
            array('id' => 3, 'lessonId' => 3, 'markerId' => 1, 'questionId' => 1),
        );
        $this->mockBiz('Marker:QuestionMarkerService', array(
            array('functionName' => 'findQuestionMarkersByMarkerIds', 'runTimes' => 1, 'returnValue' => $questionMarkers),
            array('functionName' => 'getQuestionMarker', 'runTimes' => 1, 'returnValue' => $expectedQMarker),
        ));
    }

    private function mockQuestionMarkerResultDao()
    {
        $this->mockBiz('Marker:QuestionMarkerResultDao', array(
            array('functionName' => 'countDistinctUserIdByQuestionMarkerIdAndTaskId', 'runTimes' => 3, 'returnValue' => 3),
            array('functionName' => 'count', 'runTimes' => 3, 'returnValue' => 20),
            array('functionName' => 'countDistinctUserIdByTaskId', 'runTimes' => 1, 'returnValue' => 5),
        ));
    }

    private function mockQuestionMarkerResultService($expectedResults)
    {
        $this->mockBiz('Marker:QuestionMarkerResultService', array(
            array('functionName' => 'findByTaskIdAndQuestionMarkerId', 'runTimes' => 1, 'returnValue' => $expectedResults),
        ));
    }

    protected function getReportService()
    {
        return $this->createService('Marker:ReportService');
    }
}
