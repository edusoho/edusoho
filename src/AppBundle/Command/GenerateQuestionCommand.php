<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class GenerateQuestionCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('topxia:generate-question')
            ->addArgument('target', InputArgument::REQUIRED, 'target')
            ->addArgument('type', InputArgument::REQUIRED, 'type')
            ->addArgument('count', InputArgument::REQUIRED, 'count')
            ->addArgument('difficulty', InputArgument::REQUIRED, 'difficulty');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();

        $target = $input->getArgument('target');
        $type = $input->getArgument('type');
        $count = $input->getArgument('count');
        $difficulty = $input->getArgument('difficulty');

        switch ($type) {
            case 'single_choice':
                return $this->generateSingleChoiceQuestions($target, $count, $difficulty);
            case 'choice':
                return $this->generateChoiceQuestions($target, $count, $difficulty);
            case 'fill':
                return $this->generateFillQuestions($target, $count, $difficulty);
            case 'determine':
                return $this->generateDetermineQuestions($target, $count, $difficulty);
            case 'essay':
                return $this->generateEssayQuestions($target, $count, $difficulty);
            case 'material':
                return $this->generateMaterialQuestions($target, $count, $difficulty);
        }
    }

    private function generateSingleChoiceQuestions($target, $count, $difficulty = null, $parentId = 0)
    {
        $questions = array();
        for ($i = 0; $i < $count; ++$i) {
            $answers = array('0', '1', '2', '3');
            shuffle($answers);
            $answers = array_slice($answers, 0, 1);

            $question = array(
                'type' => 'choice',
                'stem' => "单选题选择题 {$i}，正确答案：".implode('/', $answers),
                'choices' => array(
                    '选项0',
                    '选项1',
                    '选项2',
                    '选项3',
                ),
                'answer' => $answers,
                'target' => $target,
                'difficulty' => empty($difficulty) ? 'normal' : $difficulty,
            );

            $questions[] = $this->getQuestionService()->createQuestion($question);
        }

        return $questions;
    }

    private function generateChoiceQuestions($target, $count, $difficulty = null, $parentId = 0)
    {
        $questions = array();
        for ($i = 0; $i < $count; ++$i) {
            $answers = array('0', '1', '2', '3');
            $answerCount = rand(2, 4);
            shuffle($answers);
            $answers = array_slice($answers, 0, $answerCount);

            $question = array(
                'type' => 'choice',
                'parentId' => $parentId,
                'stem' => "多选题选择题 {$i}，正确答案：".implode('/', $answers),
                'choices' => array(
                    '多选题选项0',
                    '多选题选项1',
                    '多选题选项2',
                    '多选题选项3',
                ),
                'answer' => $answers,
                'target' => $target,
                'difficulty' => empty($difficulty) ? 'normal' : $difficulty,
            );

            $questions[] = $this->getQuestionService()->createQuestion($question);
        }

        return $questions;
    }

    private function generateDetermineQuestions($target, $count, $difficulty = null, $parentId = 0)
    {
        $questions = array();
        for ($i = 0; $i < $count; ++$i) {
            $answer = rand(0, 1);
            $question = array(
                'type' => 'determine',
                'parentId' => $parentId,
                'stem' => "判断题 {$i}，答案：",
                'target' => $target,
                'answer' => array($answer),
                'difficulty' => empty($difficulty) ? 'normal' : $difficulty,
            );

            $questions[] = $this->getQuestionService()->createQuestion($question);
        }

        return $questions;
    }

    private function generateFillQuestions($target, $count, $difficulty = null, $parentId = 0)
    {
        $questions = array();
        for ($i = 0; $i < $count; ++$i) {
            $question = array(
                'type' => 'fill',
                'parentId' => $parentId,
                'stem' => "填空题 {$i}： [[答案1|答案2]].",
                'target' => $target,
                'difficulty' => empty($difficulty) ? 'normal' : $difficulty,
            );

            $questions[] = $this->getQuestionService()->createQuestion($question);
        }

        return $questions;
    }

    private function generateEssayQuestions($target, $count, $difficulty = null, $parentId = 0)
    {
        $questions = array();
        for ($i = 0; $i < $count; ++$i) {
            $question = array(
                'type' => 'essay',
                'parentId' => $parentId,
                'stem' => "问答题 {$i}",
                'target' => $target,
                'answer' => array('答案'),
                'difficulty' => empty($difficulty) ? 'normal' : $difficulty,
            );

            $questions[] = $this->getQuestionService()->createQuestion($question);
        }

        return $questions;
    }

    private function generateMaterialQuestions($target, $count, $difficulty = null)
    {
        $questions = array();
        for ($i = 0; $i < $count; ++$i) {
            $question = array(
                'type' => 'material',
                'stem' => "材料题 {$i}",
                'target' => $target,
                'difficulty' => empty($difficulty) ? 'normal' : $difficulty,
            );
            $question = $this->getQuestionService()->createQuestion($question);
            $questions[] = $question;

            $this->generateChoiceQuestions($target, 3, $difficulty, $question['id']);
            $this->generateEssayQuestions($target, 2, $difficulty, $question['id']);
        }

        return $questions;
    }

    protected function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question:QuestionService');
    }
}
