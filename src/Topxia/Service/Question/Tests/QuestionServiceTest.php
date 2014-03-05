<?php

namespace Topxia\Service\Question\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Common\ArrayToolkit;

class QuestionServiceTest extends BaseTestCase
{

    public function testSingleJudgeChoiceQuestions()
    {
        $question = array(
            'type' => 'single_choice',
            'stem' => 'test single choice question 1.',
            'choices' => array(
                'question 1 -> choice 1',
                'question 1 -> choice 2',
                'question 1 -> choice 3',
                'question 1 -> choice 4',
            ),
            'answer' => array(1),
            'target' => 'course-1',
        );
        $question = $this->getQuestionService()->createQuestion($question);

        $result = $this->getQuestionService()->judgeQuestions(array(
            $question['id'] => array(),
        ));
        $this->assertEquals('noAnswer', $result[$question['id']]['status']);

        $result = $this->getQuestionService()->judgeQuestions(array(
            $question['id'] => array(1),
        ));
        $this->assertEquals('right', $result[$question['id']]['status']);

        $result = $this->getQuestionService()->judgeQuestions(array(
            $question['id'] => array(0),
        ));
        $this->assertEquals('wrong', $result[$question['id']]['status']);


        $result = $this->getQuestionService()->judgeQuestions(array(
            $question['id'] => array(2),
        ));
        $this->assertEquals('wrong', $result[$question['id']]['status']);
    }


    public function testJudgeChoiceQuestions()
    {
        $question = array(
            'type' => 'choice',
            'stem' => 'test choice question 1.',
            'choices' => array(
                'question 1 -> choice 1',
                'question 1 -> choice 2',
                'question 1 -> choice 3',
                'question 1 -> choice 4',
            ),
            'answer' => array(0, 1),
            'target' => 'course-1',
        );
        $question = $this->getQuestionService()->createQuestion($question);

        $result = $this->getQuestionService()->judgeQuestions(array(
            $question['id'] => array(),
        ));
        $this->assertEquals('noAnswer', $result[$question['id']]['status']);

        $result = $this->getQuestionService()->judgeQuestions(array(
            $question['id'] => array(0, 1),
        ));
        $this->assertEquals('right', $result[$question['id']]['status']);

        $result = $this->getQuestionService()->judgeQuestions(array(
            $question['id'] => array(0),
        ));
        $this->assertEquals('partRight', $result[$question['id']]['status']);

        $result = $this->getQuestionService()->judgeQuestions(array(
            $question['id'] => array(1),
        ));
        $this->assertEquals('partRight', $result[$question['id']]['status']);

        $result = $this->getQuestionService()->judgeQuestions(array(
            $question['id'] => array(0, 1, 2),
        ));
        $this->assertEquals('wrong', $result[$question['id']]['status']);

        $result = $this->getQuestionService()->judgeQuestions(array(
            $question['id'] => array(2),
        ));
        $this->assertEquals('wrong', $result[$question['id']]['status']);

        $result = $this->getQuestionService()->judgeQuestions(array(
            $question['id'] => array(0, 2),
        ));
        $this->assertEquals('wrong', $result[$question['id']]['status']);
    }

    public function testJudgeChoiceQuestionsWithPartRightPercentage()
    {
        $question = array(
            'type' => 'choice',
            'stem' => 'test choice question 1.',
            'choices' => array(
                'question 1 -> choice 1',
                'question 1 -> choice 2',
                'question 1 -> choice 3',
                'question 1 -> choice 4',
            ),
            'answer' => array(0, 1, 2, 3),
            'target' => 'course-1',
        );
        $question = $this->getQuestionService()->createQuestion($question);
        $result = $this->getQuestionService()->judgeQuestions(array(
            $question['id'] => array(0),
        ));
        $this->assertEquals('partRight', $result[$question['id']]['status']);
        $this->assertEquals(25, $result[$question['id']]['percentage']);

        $result = $this->getQuestionService()->judgeQuestions(array(
            $question['id'] => array(0, 1),
        ));
        $this->assertEquals('partRight', $result[$question['id']]['status']);
        $this->assertEquals(50, $result[$question['id']]['percentage']);

        $result = $this->getQuestionService()->judgeQuestions(array(
            $question['id'] => array(0, 1, 2),
        ));
        $this->assertEquals('partRight', $result[$question['id']]['status']);
        $this->assertEquals(75, $result[$question['id']]['percentage']);
    }

    public function testJudgeDetermineQuestions()
    {
        $question = array(
            'type' => 'determine',
            'stem' => 'test determine question 1.',
            'answer' => array(1),
            'target' => 'course-1',
        );
        $question = $this->getQuestionService()->createQuestion($question);

        $result = $this->getQuestionService()->judgeQuestions(array(
            $question['id'] => array(1),
        ));
        $this->assertEquals('right', $result[$question['id']]['status']);

        $result = $this->getQuestionService()->judgeQuestions(array(
            $question['id'] => array(0),
        ));
        $this->assertEquals('wrong', $result[$question['id']]['status']);

        $question['answer'] = array(0);
        $question = $this->getQuestionService()->createQuestion($question);

        $result = $this->getQuestionService()->judgeQuestions(array(
            $question['id'] => array(1),
        ));
        $this->assertEquals('wrong', $result[$question['id']]['status']);

        $result = $this->getQuestionService()->judgeQuestions(array(
            $question['id'] => array(0),
        ));
        $this->assertEquals('right', $result[$question['id']]['status']);
    }

    public function testJudgeFillQuestions()
    {
        $question = array(
            'type' => 'fill',
            'stem' => 'fill 1 [[aaa|bbb|ccc]], fill 2 [[ddd|eee|fff]].',
            'target' => 'course-1',
        );
        $question = $this->getQuestionService()->createQuestion($question);

        $result = $this->getQuestionService()->judgeQuestions(array(
            $question['id'] => array('aaa', 'eee'),
        ));
        $this->assertEquals('right', $result[$question['id']]['status']);

        $result = $this->getQuestionService()->judgeQuestions(array(
            $question['id'] => array('ddd', 'eee'),
        ));
        $this->assertEquals('partRight', $result[$question['id']]['status']);
        $this->assertEquals(50, $result[$question['id']]['percentage']);

        $result = $this->getQuestionService()->judgeQuestions(array(
            $question['id'] => array('aaa', 'qqq'),
        ));
        $this->assertEquals('partRight', $result[$question['id']]['status']);
        $this->assertEquals(50, $result[$question['id']]['percentage']);

        $result = $this->getQuestionService()->judgeQuestions(array(
            $question['id'] => array('qqq', 'www'),
        ));
        $this->assertEquals('wrong', $result[$question['id']]['status']);

        $result = $this->getQuestionService()->judgeQuestions(array(
            $question['id'] => array('aaa', 'eee', 'qqq'),
        ));
        $this->assertEquals('wrong', $result[$question['id']]['status']);

        $result = $this->getQuestionService()->judgeQuestions(array(
            $question['id'] => array('aaa'),
        ));
        $this->assertEquals('wrong', $result[$question['id']]['status']);

        $result = $this->getQuestionService()->judgeQuestions(array(
            $question['id'] => array(),
        ));
        $this->assertEquals('noAnswer', $result[$question['id']]['status']);
    }

    /**
     * @group current
     */
    public function testJudgeEssayQuestions()
    {
        $question = array(
            'type' => 'essay',
            'stem' => 'question.',
            'answer' => array('answer'),
            'target' => 'course-1',
        );
        $question = $this->getQuestionService()->createQuestion($question);

        $result = $this->getQuestionService()->judgeQuestions(array(
            $question['id'] => array('answer'),
        ));
        $this->assertEquals('unableJudge', $result[$question['id']]['status']);
    }


    private function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question.QuestionService');
    }

}