<?php
namespace Topxia\Service\Quiz\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Quiz\QuestionImplementor;
use Topxia\Service\Quiz\Impl\QuestionSerialize;
use Topxia\Common\ArrayToolkit;

class ChoiceQuestionImplementorImpl extends BaseService implements QuestionImplementor
{
	public function getQuestion($question)
    {
        $question = QuestionSerialize::unserialize($question);
        $question['choice'] = $this->getQuizQuestionChoiceDao()->findChoicesByQuestionIds(array($question['id']));
        $question['choice']['isAnswer'] = implode(',',$question['answer']);
        return $question;
    }

	public function createQuestion($question, $field){
		if (!empty($question['parentId'])){
            $field['parentId'] = (int) trim($question['parentId']);
        }
        $choiceField = $this->filterChoiceFields($question);
        $field['questionType'] = $choiceField['type'];
        unset($choiceField['type']);
        $result =  QuestionSerialize::unserialize(
            $this->getQuizQuestionDao()->addQuestion(QuestionSerialize::serialize($field))
        );
        $choices = array();
        foreach ($choiceField['choices'] as $key => $content) {
            $choice['questionId'] = $result['id'];
            $choice['content'] = $content;
            $choiceResult = $this->getQuizQuestionChoiceDao()->addChoice($choice);
            if (in_array($key, $choiceField['answers'])){
                $choices[] = $choiceResult;
            }
        }
        $field = array();
        $field['answer'] =  ArrayToolkit::column($choices,'id');
        return QuestionSerialize::unserialize(
            $this->getQuizQuestionDao()->updateQuestion($result['id'], QuestionSerialize::serialize($field))
        );

	}

	public function updateQuestion($question, $field){
        $choiceField = $this->filterChoiceFields($question);
        $field['questionType'] = $choiceField['type'];
        unset($choiceField['type']);
        $result =  QuestionSerialize::unserialize(
            $this->getQuizQuestionDao()->updateQuestion($question['id'], QuestionSerialize::serialize($field))
        );
        $this->getQuizQuestionChoiceDao()->deleteChoicesByQuestionIds(array($question['id']));
        $choices = array();
        foreach ($choiceField['choices'] as $key => $content) {
            $choice['questionId'] = $result['id'];
            $choice['content'] = $content;
            $choiceResult = $this->getQuizQuestionChoiceDao()->addChoice($choice);
            if (in_array($key, $choiceField['answers'])){
                $choices[] = $choiceResult;
            }
        }
        $field = array();
        $field['answer'] =  ArrayToolkit::column($choices,'id');
        return QuestionSerialize::unserialize(
            $this->getQuizQuestionDao()->updateQuestion($result['id'], QuestionSerialize::serialize($field))
        );
    }

    private function filterChoiceFields($question)
    {
        $field['choices'] = $question['choices'];
        $field['answers'] = explode('|', $question['answers']);
        if (!is_array($field['choices']) || count($field['choices']) < 1) {
            throw $this->createServiceException("choices参数不正确");
        }
        if (!is_array($field['answers']) || empty($field['answers'])) {
            throw $this->createServiceException("answers参数不正确");
        }
        if(count($field['answers']) == 1){
            $field['type'] = 'choice';
        }else{
            $field['type'] = 'single_choice';
        }
        return $field;
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

