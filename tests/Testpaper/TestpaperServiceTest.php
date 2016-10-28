<?php

namespace Topxia\Service\Question\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Common\ArrayToolkit;

class TestpaperServiceTest extends BaseTestCase
{
    
    public function testBuildTestpaperWithRandMode()
    {


    }

    protected function generateChoiceQuestions($target, $count, $difficulty = null)
    {
        $questions = array();
        for ($i=0; $i<$count; $i++) {
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
                'target' => $target,
                'difficulty' => empty($difficulty) ? 'normal' : $difficulty,
            );

            $questions[] = $this->getQuestionService()->createQuestion($question);
        }
        return $questions;
    }

    protected function generateFillQuestions($target, $count, $difficulty = null)
    {
        $questions = array();
        for ($i=0; $i<$count; $i++) {
            $question = array(
                'type' => 'fill',
                'stem' => 'fill question [[aaa]].',
                'target' => $target,
                'difficulty' => empty($difficulty) ? 'normal' : $difficulty,
            );

            $questions[] = $this->getQuestionService()->createQuestion($question);
        }
        return $questions;
    }

    protected function generateDetermineQuestions($target, $count, $difficulty = null)
    {
        $questions = array();
        for ($i=0; $i<$count; $i++) {
            $question = array(
                'type' => 'determine',
                'stem' => 'determine question.',
                'target' => $target,
                'answer' => array(0),
                'difficulty' => empty($difficulty) ? 'normal' : $difficulty,
            );

            $questions[] = $this->getQuestionService()->createQuestion($question);
        }
        return $questions;
    }

    protected function generateEssayQuestions($target, $count, $difficulty = null)
    {
        $questions = array();
        for ($i=0; $i<$count; $i++) {
            $question = array(
                'type' => 'essay',
                'stem' => 'essay question.',
                'target' => $target,
                'answer' => array('xxx'),
                'difficulty' => empty($difficulty) ? 'normal' : $difficulty,
            );

            $questions[] = $this->getQuestionService()->createQuestion($question);
        }
        return $questions;
    }

    protected function generateMaterialQuestions($target, $count, $difficulty = null)
    {
        $questions = array();
        for ($i=0; $i<$count; $i++) {
            $question = array(
                'type' => 'material',
                'stem' => 'material question.',
                'target' => $target,
                'difficulty' => empty($difficulty) ? 'normal' : $difficulty,
            );

            $questions[] = $this->getQuestionService()->createQuestion($question);
        }
        return $questions;
    }

    /* 试卷同步
    */

    public function testFindTestpapersByCopyIdAndLockedTarget()
    {
        $question = array(
            'type' => 'single_choice',
            'stem' => 'question.',
            'difficulty'=>'normal',
            'answer' => array('answer'),
            'target' => 'course-1',
            '"stem"'=>$this->getServiceKernel()->trans('测试'),
            "choices"=>array($this->getServiceKernel()->trans('爱'),$this->getServiceKernel()->trans('测'),$this->getServiceKernel()->trans('额'),$this->getServiceKernel()->trans('恶')),
            'uncertain'=>0,
            "analysis"=>'',
            "score"=>'2',
            "submission"=>'submit',
            "type"=>"choice",
            "parentId"=>0,
            'copyId'=>1,
            "answer"=>"2"
        );
        $question = $this->getQuestionService()->createQuestion($question);
        $testpaper = array('name' => 'Test',"description"=>$this->getServiceKernel()->trans('测试'),"limitedTime"=>'0',"mode"=>"rand","range"=>"course","ranges"=>array(),"counts"=>array("single_choice"=>"1","choice"=>"0","uncertain_choice"=>"0","fill"=>"0","determine"=>"0","material"=>"0"),'CopyId'=>1,'target'=>'course-1',"scores"=>array("single_choice"=>"2","uncertain_choice"=>"2","choice"=>"2","uncertain_choice"=>"2","fill"=>"2","determine"=>"2","essay"=>"2","material"=>"2"),"missScores"=>array("choice"=>0,"uncertain_choice"=>0),"percentages"=>array("simple"=>"","normal"=>"","difficulty"=>''),"target"=>'course-1',"pattern"=>"QuestionType","copyId"=>"1");
        $testpaper = $this->getTestpaperService()->createTestpaper($testpaper);
        $testpaper = $this->getTestpaperService()->findTestpapersByCopyIdAndLockedTarget(1,"('course-1')");
        $this->assertEquals('Test',$testpaper[0]['name']);
    }
    
    protected function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question.QuestionService');
    }

    protected function getTestpaperService()
    {
        return $this->getServiceKernel()->createService('Testpaper.TestpaperService');
    }

}