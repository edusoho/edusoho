<?php

namespace Biz\Testpaper\Tests;

use Biz\BaseTestCase;
use Biz\Testpaper\Pattern\QuestionTypePattern;
use Biz\Testpaper\Service\TestpaperService;

class QuestionTypePatternTest extends BaseTestCase
{
    public function testCanBuild()
    {
        $testpaper = $this->createTestpaper1();
        $items = $this->createTestpaperItem($testpaper);

        $builder = new QuestionTypePattern($this->getBiz());
        $options1 = array(
            'mode' => 'range',
            'ranges' => array('categoryId' => 0),
            'counts' => array('choice' => 1, 'fill' => 1, 'determine' => 1, 'material' => 1),
            'scores' => array('choice' => 2, 'fill' => 2, 'determine' => 2, 'material' => 2),
            'missScores' => array('choice' => 1, 'uncertain_choice' => 1),
            'bankId' => 1,
        );
        $result = $builder->canBuild($options1);
        $this->assertEquals('yes', $result['status']);
    }

    protected function createTestpaper1($customFields = array())
    {
        $fields = array(
            'name' => 'testpaper',
            'description' => 'testpaper description',
            'bankId' => 1,
            'courseId' => 0,
            'pattern' => 'questionType',
            'metas' => array(
                'ranges' => array('categoryId' => 0),
            ),
            'type' => 'testpaper',
        );
        $fields = array_merge($fields, $customFields);

        return $this->getTestpaperService()->createTestpaper($fields);
    }

    protected function createTestpaperItem($testpaper)
    {
        $choiceQuestions = $this->generateChoiceQuestions($testpaper['bankId'], 1);
        $fillQuestions = $this->generateFillQuestions($testpaper['bankId'], 1);
        $determineQuestions = $this->generateDetermineQuestions($testpaper['bankId'], 1);
        $materialQuestions = $this->generateMaterialQuestions($testpaper['bankId'], 1);
        $subChoiceQuestions = $this->generateChoiceQuestions($testpaper['bankId'], 1, null, $materialQuestions[0]['id']);
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

    protected function generateChoiceQuestions($bankId, $count, $difficulty = null, $parentId = 0)
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
                'bankId' => $bankId,
                'difficulty' => empty($difficulty) ? 'normal' : $difficulty,
                'parentId' => $parentId,
            );

            $questions[] = $this->getQuestionService()->create($question);
        }

        return $questions;
    }

    protected function generateFillQuestions($bankId, $count, $difficulty = null, $parentId = 0)
    {
        $questions = array();
        for ($i = 0; $i < $count; ++$i) {
            $question = array(
                'type' => 'fill',
                'stem' => 'fill question [[aaa]].',
                'bankId' => $bankId,
                'difficulty' => empty($difficulty) ? 'normal' : $difficulty,
                'parentId' => $parentId,
            );

            $questions[] = $this->getQuestionService()->create($question);
        }

        return $questions;
    }

    protected function generateDetermineQuestions($bankId, $count, $difficulty = null, $parentId = 0)
    {
        $questions = array();
        for ($i = 0; $i < $count; ++$i) {
            $question = array(
                'type' => 'determine',
                'stem' => 'determine question.',
                'bankId' => $bankId,
                'answer' => array(0),
                'difficulty' => empty($difficulty) ? 'normal' : $difficulty,
                'parentId' => $parentId,
            );

            $questions[] = $this->getQuestionService()->create($question);
        }

        return $questions;
    }

    protected function generateMaterialQuestions($bankId, $count, $difficulty = null)
    {
        $questions = array();
        for ($i = 0; $i < $count; ++$i) {
            $question = array(
                'type' => 'material',
                'stem' => 'material question.',
                'bankId' => $bankId,
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
