<?php

namespace Topxia\Service\Question\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Common\ArrayToolkit;

class TestpaperServiceTest extends BaseTestCase
{
    /**
     * @group current
     */
    public function testBuildTestpaperWithRandMode()
    {
        $target = 'course-1';

        $this->generateChoiceQuestions($target, 5, 'simple');
        $this->generateChoiceQuestions($target, 5, 'normal');
        $this->generateChoiceQuestions($target, 5, 'difficulty');

        $this->generateFillQuestions($target, 5, 'simple');
        $this->generateFillQuestions($target, 5, 'normal');
        $this->generateFillQuestions($target, 5, 'difficulty');

        $this->generateDetermineQuestions($target, 5, 'simple');
        $this->generateDetermineQuestions($target, 5, 'normal');

        $this->generateEssayQuestions($target, 1, 'simple');
        $this->generateEssayQuestions($target, 2, 'normal');
        $this->generateEssayQuestions($target, 1, 'difficulty');

        list($testpaper, $items) = $this->getTestpaperService()->createTestpaper(array(
            'name' => 'test paper', 
            'pattern' => 'QuestionType', 
            'mode' => 'difficulty',
            'target' => $target,
            'counts' => array(
                'essay' => 4,
                'choice' => 2,
            ),
            'scores' => array(
                'essay' => 1,
                'choice' => 2,
            ),
            'percentages' => array(
                'simple' => 30,
                'normal' => 40,
                'difficulty' => 30,
            ),
        ));

    }

    public function testCanBuildTestpaper()
    {
        $target = 'course-1';
        $questions = $this->generateEssayQuestions($target, 5, 'simple');
        $questions = $this->generateMaterialQuestions($target, 5, 'simple');

        $result = $this->getTestpaperService()->canBuildTestpaper('QuestionType', array(
            'target' => $target,
            'mode' => 'rand',
            'counts' => array(
                'essay' => 4,
            ),
            'scores' => array(
                'essay' => 1,
            )
        ));

        $this->assertEquals('yes', $result['status']);

        $result = $this->getTestpaperService()->canBuildTestpaper('QuestionType', array(
            'target' => $target,
            'mode' => 'rand',
            'counts' => array(
                'essay' => 6,
                'material' => 4,
            ),
            'scores' => array(
                'essay' => 1,
                'material' => 1,
            )
        ));

        $this->assertEquals('no', $result['status']);
        $this->assertEquals(1, $result['missing']['essay']);

        $result = $this->getTestpaperService()->canBuildTestpaper('QuestionType', array(
            'target' => $target,
            'mode' => 'rand',
            'counts' => array(
                'essay' => 6,
                'material' => 6,
            ),
            'scores' => array(
                'essay' => 1,
                'material' => 1,
            )
        ));

        $this->assertEquals('no', $result['status']);
        $this->assertEquals(1, $result['missing']['essay']);
        $this->assertEquals(1, $result['missing']['material']);

        $result = $this->getTestpaperService()->canBuildTestpaper('QuestionType', array(
            'target' => $target,
            'mode' => 'rand',
            'counts' => array(
                'essay' => 5,
                'material' => 5,
            ),
            'scores' => array(
                'essay' => 1,
                'material' => 1,
            )
        ));

        $this->assertEquals('yes', $result['status']);
    }

    private function generateChoiceQuestions($target, $count, $difficulty = null)
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

    private function generateFillQuestions($target, $count, $difficulty = null)
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

    private function generateDetermineQuestions($target, $count, $difficulty = null)
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

    private function generateEssayQuestions($target, $count, $difficulty = null)
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

    private function generateMaterialQuestions($target, $count, $difficulty = null)
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

    private function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question.QuestionService');
    }

    private function getTestpaperService()
    {
        return $this->getServiceKernel()->createService('Testpaper.TestpaperService');
    }
}