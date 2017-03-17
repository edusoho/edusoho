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
        $this->assertEquals('none', $result[$question['id']]['status']);
    }

    /*
        问题数据同步
    */

    public function testFindQuestionsByCopyIdAndLockedTarget()
    {
        $question = array(
            'type' => 'single_choice',
            'stem' => 'question.',
            'difficulty'=>'normal',
            'answer' => array('answer'),
            'target' => 'course-1',
            '"stem"'=>'测试',
            "choices"=>array("爱","测","额","恶"),
            'uncertain'=>0,
            "analysis"=>'',
            "score"=>'2',
            "submission"=>'submit',
            "type"=>"choice",
            "parentId"=>0,
            'copyId'=>1,
        );

        $question = $this->getQuestionService()->createQuestion($question);
        $this->assertEquals('question.',$question['stem']);
        $question = $this->getQuestionService()->findQuestionsByCopyIdAndLockedTarget(1, array("course-1"));
        $this->assertEquals('question.',$question[0]['stem']);
    }

    public function testFindQuestionsCountByParentId()
    {
       $question = array(
            'type' => 'single_choice',
            'stem' => 'question.',
            'difficulty'=>'normal',
            'answer' => array('answer'),
            'target' => 'course-1',
            '"stem"'=>'测试',
            "choices"=>array("爱","测","额","恶"),
            'uncertain'=>0,
            "analysis"=>'',
            "score"=>'2',
            "submission"=>'submit',
            "type"=>"choice",
            "parentId"=>0,
            'copyId'=>1,
        );
        $question1 = array(
            'type' => 'single_choice',
            'stem' => 'question.',
            'difficulty'=>'normal',
            'answer' => array('answer'),
            'target' => 'course-1',
            '"stem"'=>'测试',
            "choices"=>array("爱","测","额","恶"),
            'uncertain'=>0,
            "analysis"=>'',
            "score"=>'2',
            "submission"=>'submit',
            "type"=>"choice",
            "parentId"=>1,
            'copyId'=>1,
        );
        $question = $this->getQuestionService()->createQuestion($question);
        $question = $this->getQuestionService()->createQuestion($question1);
        $this->assertEquals('question.',$question['stem']); 
        $count = $this->getQuestionService()->findQuestionsCountByParentId(1);
        $this->assertEquals(1,$count); 
    }

    public function testDeleteQuestionsByParentId()
    {
        $question = array(
            'type' => 'single_choice',
            'stem' => 'question.',
            'difficulty'=>'normal',
            'answer' => array('answer'),
            'target' => 'course-1',
            '"stem"'=>'测试',
            "choices"=>array("爱","测","额","恶"),
            'uncertain'=>0,
            "analysis"=>'',
            "score"=>'2',
            "submission"=>'submit',
            "type"=>"choice",
            "parentId"=>0,
            'copyId'=>1,
        );
        $question1 = array(
            'type' => 'single_choice',
            'stem' => 'question.',
            'difficulty'=>'normal',
            'answer' => array('answer'),
            'target' => 'course-1',
            '"stem"'=>'测试',
            "choices"=>array("爱","测","额","恶"),
            'uncertain'=>0,
            "analysis"=>'',
            "score"=>'2',
            "submission"=>'submit',
            "type"=>"choice",
            "parentId"=>1,
            'copyId'=>1,
        );
        $question = $this->getQuestionService()->createQuestion($question);
        $question = $this->getQuestionService()->createQuestion($question1);
        $this->assertEquals('question.',$question['stem']);
        $count = $this->getQuestionService()->deleteQuestionsByParentId(1);
        $this->assertEquals(1,$count);
    }

    protected function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question.QuestionService');
    }

}