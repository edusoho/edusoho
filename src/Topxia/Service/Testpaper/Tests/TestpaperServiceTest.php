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

    public function testAddTestpaper()
    {
        $testpaper = array('name' => 'Test');
        $testpaper = $this->getTestpaperService()->addTestpaper($testpaper);
        $this->assertEquals('Test',$testpaper['name']);
    }

    public function testUpdateTestpaperByPId()
    {
        $testpaper = array('name' => 'Test','pId'=>1);
        $testpaper = $this->getTestpaperService()->addTestpaper($testpaper);
        $this->assertEquals('Test',$testpaper['name']);
        $count = $this->getTestpaperService()->updateTestpaperByPId(1,array('name'=>'Test2'));
        $this->assertEquals(1,$count);    
    }

    public function testDeleteTestpaperByPId()
    {
        $testpaper = array('name' => 'Test','pId'=>1);
        $testpaper = $this->getTestpaperService()->addTestpaper($testpaper);
        $this->assertEquals('Test',$testpaper['name']);
        $count = $this->getTestpaperService()->deleteTestpaperByPId(1);;
        $this->assertEquals(1,$count);
    }

    public function testFindTestpaperByPId()
    {
        $testpaper = array('name' => 'Test','pId'=>1);
        $testpaper = $this->getTestpaperService()->addTestpaper($testpaper);
        $this->assertEquals('Test',$testpaper['name']);
        $testpaper = $this->getTestpaperService()->findTestpaperByPId(1);
        $this->assertEquals('Test',$testpaper[0]['name']);
    }

    public function testCreateTestpaperItem()
    {
        $testpaperItem = array('questionType'=>'single_choice');
        $testpaperItem = $this->getTestpaperService()->createTestpaperItem($testpaperItem);
        $this->assertEquals('single_choice',$testpaperItem['questionType']);
    }

    public function testDeleteTestpaperItemByPId()
    {
        $testpaperItem = array('questionType'=>'single_choice','pId'=>1);
        $testpaperItem = $this->getTestpaperService()->createTestpaperItem($testpaperItem);
        $this->assertEquals('single_choice',$testpaperItem['questionType']);
        $count = $this->getTestpaperService()->deleteTestpaperItemByPId(1);
        $this->assertEquals(1,$count);
    }

    public function testDeleteTestpaperItemByTestId()
    {
        $testpaperItem = array('questionType'=>'single_choice','pId'=>1,'testId'=>1);
        $testpaperItem = $this->getTestpaperService()->createTestpaperItem($testpaperItem);
        $this->assertEquals('single_choice',$testpaperItem['questionType']);
        $count = $this->getTestpaperService()->deleteTestpaperItemByTestId(1);
        $this->assertEquals(1,$count);
    }

    public function testUpdateTestpaperItemByPId()
    {
        $testpaperItem = array('questionType'=>'single_choice','pId'=>1,'testId'=>1);
        $testpaperItem = $this->getTestpaperService()->createTestpaperItem($testpaperItem);
        $this->assertEquals('single_choice',$testpaperItem['questionType']);
        $count = $this->getTestpaperService()->updateTestpaperItemByPId(1,array('questionType'=>'fill'));
        $this->assertEquals(1,$count);
    }

    public function testUpdateTestpaperAndTestpaperItemByTarget()
    {
        $testpaper = array('name' => 'Test','target'=>'course-1');
        $testpaper = $this->getTestpaperService()->addTestpaper($testpaper);
        $this->assertEquals('Test',$testpaper['name']);
        $testpaperItem = array('questionType'=>'single_choice','testId'=>$testpaper['id']);
        $testpaperItem = $this->getTestpaperService()->createTestpaperItem($testpaperItem);
        $this->assertEquals('single_choice',$testpaperItem['questionType']);
        $count = $this->getTestpaperService()->updateTestpaperAndTestpaperItemByTarget(1,array('pId'=>1));
        $this->assertEquals(1,$count);
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