<?php

namespace Tests\Unit\Question\Event;

use Biz\BaseTestCase;
use Biz\Question\Event\QuestionSyncSubscriber;
use Codeages\Biz\Framework\Event\Event;

class QuestionSyncSubscriberTest extends BaseTestCase
{
    public function testGetSubscribedEvents()
    {
        $this->assertArrayEquals(
            array(
                'question.create' => array('onQuestionCreate', 1),
                'question.update' => array('onQuestionUpdate', 1),
                'question.delete' => array('onQuestionDelete', 1),
            ),
            QuestionSyncSubscriber::getSubscribedEvents()
        );
    }

    public function testOnQuestionCreate()
    {
        $courseSetDao = $this->mockCourseSetDao();
        $courseService = $this->mockCourseService();

        $questionService = $this->mockBiz(
            'Question:QuestionService',
            array(
                array(
                    'functionName' => 'search',
                    'withParams' => array(array('copyId' => 23213), array(), 0, PHP_INT_MAX),
                    'returnValue' => array(
                        array(
                            'id' => 9991,
                            'courseSetId' => 2220,
                        ),
                        array(
                            'id' => 9992,
                            'courseSetId' => 2221,
                        ),
                    ),
                ),
                array(
                    'functionName' => 'search',
                    'withParams' => array(
                        array('copyId' => 78654, 'courseSetIds' => array(2220, 2221)),
                        array(),
                        0,
                        PHP_INT_MAX,
                    ),
                    'returnValue' => array(
                        array(
                            'id' => 9991,
                            'courseSetId' => 2220,
                        ),
                        array(
                            'id' => 9992,
                            'courseSetId' => 2221,
                        ),
                    ),
                ),
                array(
                    'functionName' => 'batchCreateQuestions',
                    'withParams' => array(
                        array(
                            array(
                                'copyId' => 78654,
                                'courseSetId' => 2220,
                                'courseId' => 1213,
                                'lessonId' => 0,
                                'parentId' => 9991,
                                'createdUserId' => '1',
                                'updatedUserId' => '1',
                            ),
                            array(
                                'copyId' => 78654,
                                'courseSetId' => 2221,
                                'courseId' => 1214,
                                'lessonId' => 0,
                                'parentId' => 9992,
                                'createdUserId' => '1',
                                'updatedUserId' => '1',
                            ),
                        ),
                    ),
                ),
                array(
                    'functionName' => 'get',
                    'withParams' => array(23213),
                    'returnValue' => array(
                        'id' => 23213,
                        'subCount' => 90,
                    ),
                ),
                array(
                    'functionName' => 'updateCopyQuestionsSubCount',
                    'withParams' => array(23213, 90),
                ),
            )
        );

        $question = array(
            'id' => 78654,
            'courseId' => 1212,
            'courseSetId' => 2222,
            'lessonId' => 7654,
            'parentId' => 23213,
            'copyId' => 0,
        );

        $event = new Event($question);
        $subscriber = new QuestionSyncSubscriber($this->biz);
        $result = $subscriber->onQuestionCreate($event);

        $courseSetDao->shouldHaveReceived('findCourseSetsByParentIdAndLocked');
        $courseService->shouldHaveReceived('findCoursesByCourseSetIds');
        $questionService->shouldHaveReceived('search')->times(2);
        $questionService->shouldHaveReceived('batchCreateQuestions');
        $questionService->shouldHaveReceived('get');
        $questionService->shouldHaveReceived('updateCopyQuestionsSubCount');

        $this->assertNull($result);
    }

    public function testOnQuestionUpdate()
    {
        $courseSetDao = $this->mockCourseSetDao();
        $courseService = $this->mockCourseService();

        $questionService = $this->mockBiz(
            'Question:QuestionService',
            array(
                array(
                    'functionName' => 'search',
                    'withParams' => array(array('copyId' => 23213), array(), 0, PHP_INT_MAX),
                    'returnValue' => array(
                        array(
                            'id' => 9991,
                            'courseSetId' => 2220,
                        ),
                        array(
                            'id' => 9992,
                            'courseSetId' => 2221,
                        ),
                    ),
                ),
                array(
                    'functionName' => 'search',
                    'withParams' => array(
                        array('copyId' => 78654, 'courseSetIds' => array(2220, 2221)),
                        array(),
                        0,
                        PHP_INT_MAX,
                    ),
                    'returnValue' => array(
                        array(
                            'id' => 9991,
                            'courseSetId' => 2220,
                        ),
                        array(
                            'id' => 9992,
                            'courseSetId' => 2221,
                        ),
                    ),
                ),
                array(
                    'functionName' => 'update',
                    'withParams' => array(9991, array('courseId' => 1213, 'lessonId' => 0)),
                ),
                array(
                    'functionName' => 'update',
                    'withParams' => array(9992, array('courseId' => 1214, 'lessonId' => 0)),
                ),
            )
        );

        $question = array(
            'id' => 78654,
            'courseId' => 1212,
            'courseSetId' => 2222,
            'lessonId' => 7654,
            'parentId' => 23213,
            'copyId' => 0,
        );

        $event = new Event($question);
        $subscriber = new QuestionSyncSubscriber($this->biz);
        $result = $subscriber->onQuestionUpdate($event);

        $courseSetDao->shouldHaveReceived('findCourseSetsByParentIdAndLocked');
        $courseService->shouldHaveReceived('findCoursesByCourseSetIds');
        $questionService->shouldHaveReceived('search')->times(1);
        $questionService->shouldHaveReceived('update')->times(2);

        $this->assertNull($result);
    }

    public function testOnQuestionDelete()
    {
        $courseSetDao = $this->mockCourseSetDao();

        $questionService = $this->mockBiz(
            'Question:QuestionService',
            array(
                array(
                    'functionName' => 'search',
                    'withParams' => array(array('copyId' => 23213), array(), 0, PHP_INT_MAX),
                    'returnValue' => array(
                        array(
                            'id' => 9991,
                            'courseSetId' => 2220,
                        ),
                        array(
                            'id' => 9992,
                            'courseSetId' => 2221,
                        ),
                    ),
                ),
                array(
                    'functionName' => 'search',
                    'withParams' => array(
                        array('copyId' => 78654, 'courseSetIds' => array(2220, 2221)),
                        array(),
                        0,
                        PHP_INT_MAX,
                    ),
                    'returnValue' => array(
                        array(
                            'id' => 9991,
                            'courseSetId' => 2220,
                        ),
                        array(
                            'id' => 9992,
                            'courseSetId' => 2221,
                        ),
                    ),
                ),
                array(
                    'functionName' => 'delete',
                    'withParams' => array(9991),
                ),
                array(
                    'functionName' => 'delete',
                    'withParams' => array(9992),
                ),
            )
        );

        $question = array(
            'id' => 78654,
            'courseId' => 1212,
            'courseSetId' => 2222,
            'lessonId' => 7654,
            'parentId' => 23213,
            'copyId' => 0,
        );

        $event = new Event($question);
        $subscriber = new QuestionSyncSubscriber($this->biz);
        $result = $subscriber->onQuestionDelete($event);

        $courseSetDao->shouldHaveReceived('findCourseSetsByParentIdAndLocked');
        $questionService->shouldHaveReceived('search')->times(1);
        $questionService->shouldHaveReceived('delete')->times(2);

        $this->assertNull($result);
    }

    private function mockCourseSetDao()
    {
        return $this->mockBiz(
            'Course:CourseSetDao',
            array(
                array(
                    'functionName' => 'findCourseSetsByParentIdAndLocked',
                    'withParams' => array(2222, 1),
                    'returnValue' => array(
                        array(
                            'id' => 2220,
                        ),
                        array(
                            'id' => 2221,
                        ),
                    ),
                ),
            )
        );
    }

    private function mockCourseService()
    {
        return $this->mockBiz(
            'Course:CourseService',
            array(
                array(
                    'functionName' => 'findCoursesByCourseSetIds',
                    'withParams' => array(array(2220, 2221)),
                    'returnValue' => array(
                        array(
                            'id' => 1213,
                            'courseSetId' => 2220,
                        ),
                        array(
                            'id' => 1214,
                            'courseSetId' => 2221,
                        ),
                    ),
                ),
            )
        );
    }
}
