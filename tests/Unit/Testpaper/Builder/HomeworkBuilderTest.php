<?php

namespace Biz\Testpaper\Tests;

use Biz\BaseTestCase;
use AppBundle\Common\TimeMachine;
use Biz\Testpaper\Builder\HomeworkBuilder;
use Biz\Testpaper\Service\TestpaperService;

class HomeworkBuilderTest extends BaseTestCase
{
    public function testCanBuild()
    {
        $testpaper = $this->createTestpaper1();
        $items = $this->createTestpaperItem($testpaper);

        $builder = new HomeworkBuilder($this->getBiz());
        $options1 = array(
            'mode' => 'range',
            'ranges' => array('courseId' => 0),
            'counts' => array('choice' => 1, 'fill' => 1, 'determine' => 1, 'material' => 1),
            'scores' => array('choice' => 2, 'fill' => 2, 'determine' => 2, 'material' => 2),
            'missScores' => array('choice' => 1, 'uncertain_choice' => 1),
            'courseSetId' => 1,
        );
        $result = $builder->canBuild($options1);
        $this->assertEquals('yes', $result['status']);
    }

    public function testUpdateSubmitedResult()
    {
        $mockedTestpaperService = $this->mockBiz(
            'Testpaper:TestpaperService',
            array(
                array(
                    'functionName' => 'getTestpaperResult',
                    'withParams' => array(111),
                    'returnValue' => array(
                        'id' => 222,
                        'testId' => 111,
                        'type' => 'home',
                    ),
                ),
                array(
                    'functionName' => 'getTestpaperByIdAndType',
                    'withParams' => array(),
                    'returnValue' => array(
                        'id' => 222,
                        'itemCount' => 5,
                    ),
                ),
                array(
                    'functionName' => 'findItemsByTestId',
                    'withParams' => array(111),
                    'returnValue' => array(
                        array(
                            'id' => 1,
                        ),
                    ),
                ),
                array(
                    'functionName' => 'findItemResultsByResultId',
                    'withParams' => array(),
                    'returnValue' => array(
                        'itemResults' => 'result',
                    ),
                ),
                array(
                    'functionName' => 'sumScore',
                    'withParams' => array(array('itemResults' => 'result')),
                    'returnValue' => array(
                        'sumScore' => 9,
                        'rightItemCount' => 7,
                    ),
                ),
                array(
                    'functionName' => 'updateTestpaperResult',
                    'withParams' => array(),
                    'returnValue' => 123,
                ),
            )
        );

        $this->mockBiz(
            'Question:QuestionService',
            array(
                array(
                    'functionName' => 'hasEssay',
                    'withParams' => array(),
                    'returnValue' => false,
                ),
            )
        );
        TimeMachine::setMockedTime(1525162415);
        $builder = new HomeworkBuilder($this->getBiz());
        $result = $builder->updateSubmitedResult(111, 1525162415, null);

        $mockedTestpaperService->shouldHaveReceived('getTestpaperResult')->times(1);
        $mockedTestpaperService->shouldHaveReceived('findItemResultsByResultId')->times(1);
        $mockedTestpaperService->shouldHaveReceived('sumScore')->times(1);
        $mockedTestpaperService->shouldHaveReceived('updateTestpaperResult')->times(1);

        $this->assertEquals(123, $result);
    }

    protected function createTestpaper1($customFields = array())
    {
        $fields = array(
            'name' => 'testpaper',
            'description' => 'testpaper description',
            'courseSetId' => 1,
            'courseId' => 0,
            'pattern' => 'questionType',
            'metas' => array(
                'ranges' => array('courseId' => 0),
            ),
            'type' => 'testpaper',
        );
        $fields = array_merge($fields, $customFields);

        return $this->getTestpaperService()->createTestpaper($fields);
    }

    protected function createTestpaperItem($testpaper)
    {
        $choiceQuestions = $this->generateChoiceQuestions($testpaper['courseSetId'], 1);
        $fillQuestions = $this->generateFillQuestions($testpaper['courseSetId'], 1);
        $determineQuestions = $this->generateDetermineQuestions($testpaper['courseSetId'], 1);
        $materialQuestions = $this->generateMaterialQuestions($testpaper['courseSetId'], 1);
        $subChoiceQuestions = $this->generateChoiceQuestions($testpaper['courseSetId'], 1, null, $materialQuestions[0]['id']);
        $questions = array_merge($choiceQuestions, $fillQuestions, $determineQuestions, $materialQuestions, $subChoiceQuestions);

        $items = array();
        $seq = 1;
        foreach ($questions as $question) {
            $fields = array(
                'testId' => $testpaper['id'],
                'seq' => $seq,
                'questionId' => $question['id'],
                'questionType' => $question['type'],
                'parentId' => $question['parentId'],
                'score' => $question['score'],
                'missScore' => 0,
                'type' => $testpaper['type'],
            );
            $items[] = $this->getTestpaperService()->createItem($fields);
            ++$seq;
        }

        return $items;
    }

    protected function generateChoiceQuestions($courseId, $count, $difficulty = null, $parentId = 0)
    {
        $questions = array();
        for ($i = 0; $i < $count; ++$i) {
            $question = array(
                'type' => 'choice',
                'stem' => 'test single choice question.',
                'choices' => array(
                    'question -> choice 1',
                    'question -> choice 2',
                    'question -> choice 3',
                    'question -> choice 4',
                ),
                'answer' => array(1, 2),
                'courseSetId' => $courseId,
                'target' => 'course/'.$courseId,
                'difficulty' => empty($difficulty) ? 'normal' : $difficulty,
                'parentId' => $parentId,
            );

            $questions[] = $this->getQuestionService()->create($question);
        }

        return $questions;
    }

    protected function generateFillQuestions($courseId, $count, $difficulty = null, $parentId = 0)
    {
        $questions = array();
        for ($i = 0; $i < $count; ++$i) {
            $question = array(
                'type' => 'fill',
                'stem' => 'fill question [[aaa]].',
                'target' => 'course/'.$courseId,
                'courseSetId' => $courseId,
                'difficulty' => empty($difficulty) ? 'normal' : $difficulty,
                'parentId' => $parentId,
            );

            $questions[] = $this->getQuestionService()->create($question);
        }

        return $questions;
    }

    protected function generateDetermineQuestions($courseId, $count, $difficulty = null, $parentId = 0)
    {
        $questions = array();
        for ($i = 0; $i < $count; ++$i) {
            $question = array(
                'type' => 'determine',
                'stem' => 'determine question.',
                'target' => 'course/'.$courseId,
                'courseSetId' => $courseId,
                'answer' => array(0),
                'difficulty' => empty($difficulty) ? 'normal' : $difficulty,
                'parentId' => $parentId,
            );

            $questions[] = $this->getQuestionService()->create($question);
        }

        return $questions;
    }

    protected function generateMaterialQuestions($courseId, $count, $difficulty = null)
    {
        $questions = array();
        for ($i = 0; $i < $count; ++$i) {
            $question = array(
                'type' => 'material',
                'stem' => 'material question.',
                'target' => 'course/'.$courseId,
                'courseSetId' => $courseId,
                'difficulty' => empty($difficulty) ? 'normal' : $difficulty,
            );

            $questions[] = $this->getQuestionService()->create($question);
        }

        return $questions;
    }

    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getTestpaperResultDao()
    {
        return $this->createDao('Testpaper:TestpaperResultDao');
    }
}
