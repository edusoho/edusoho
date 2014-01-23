<?php
namespace Topxia\Service\Quiz\Impl;

use Topxia\Service\Quiz\QuestionImplementor;
use Topxia\Service\Quiz\Impl\QuestionSerialize;
use Topxia\Common\ArrayToolkit;

class ChoiceQuestionImplementorImpl extends BaseQuestionImplementor implements QuestionImplementor
{
	public function getQuestion($question)
    {
        $question = QuestionSerialize::unserialize($question);
        $question['choices'] = $this->getQuizQuestionChoiceDao()->findChoicesByQuestionIds(array($question['id']));
        return $question;
    }

	public function createQuestion($fields) 
    {
        $choices = $this->splitQuestionChoices($fields);

        $fields['metas'] = array('choices' => $choices);
        $fields['answer'] = $this->splitQuestionAnswers($fields, $choices);
        $fields['type'] = count($fields['answer']) > 1 ? 'choice' : 'single_choice';
        $fields = $this->filterQuestionFields($fields);

        $question =  QuestionSerialize::unserialize(
            $this->getQuizQuestionDao()->addQuestion(QuestionSerialize::serialize($fields))
        );

        $choices = $this->addQuestionChoices($question, $choices);

        return $question;
	}

	public function updateQuestion($id, $fields)
    {
        $choices = $this->splitQuestionChoices($fields);

        $fields['answer'] = $this->splitQuestionAnswers($fields, $choices);
        $fields['type'] = count($fields['answer']) > 1 ? 'choice' : 'single_choice';
        $fields = $this->filterQuestionFields($fields);

        $question =  QuestionSerialize::unserialize(
            $this->getQuizQuestionDao()->updateQuestion($id, QuestionSerialize::serialize($fields))
        );

        $this->getQuizQuestionChoiceDao()->deleteChoicesByQuestionIds(array($id));
        $choices = $this->addQuestionChoices($question, $choices);

        return $question;
    }

    private function splitQuestionChoices($question)
    {
        if ( empty($question['choices']) or !is_array($question['choices']) or count($question['choices']) < 2) {
            throw $this->createServiceException("choices参数不正确");
        }

        return array_values($question['choices']);
    }

    private function splitQuestionAnswers($question, $choices)
    {
        $answers = array_unique($question['answer']);

        if (empty($answers)) {
            throw $this->createServiceException("answer参数不正确");
        }

        foreach ($answers as $answer) {
            if ($answer >= count($choices)) {
                throw $this->createServiceException("answer参数不正确");
            }
        }

        return $answers;
    }

    private function addQuestionChoices($question, $choices)
    {
        $savedChoices = array();

        foreach ($choices as $content) {
            $choice = array();
            $choice['questionId'] = $question['id'];
            $choice['content'] = $content;
            $savedChoices[] = $this->getQuizQuestionChoiceDao()->addChoice($choice);
        }

        return $savedChoices;
    }

    private function getQuizQuestionDao()
    {
        return $this->createDao('Quiz.QuizQuestionDao');
    }

    private function getQuizQuestionChoiceDao()
    {
        return $this->createDao('Quiz.QuizQuestionChoiceDao');
    }
  
}

