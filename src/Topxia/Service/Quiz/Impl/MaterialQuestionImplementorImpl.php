<?php
namespace Topxia\Service\Quiz\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Quiz\QuestionImplementor;
use Topxia\Service\Quiz\Impl\QuestionSerialize;
use Topxia\Common\ArrayToolkit;

class MaterialQuestionImplementorImpl extends BaseService implements QuestionImplementor
{
	public function getQuestion($question)
    {
        return QuestionSerialize::unserialize($question);
    }

	public function createQuestion($question, $field){
		return QuestionSerialize::unserialize(
            $this->getQuizQuestionDao()->addQuestion(QuestionSerialize::serialize($field))
        );
	}

    public function updateQuestion($question, $field){
    	return  QuestionSerialize::unserialize(
            $this->getQuizQuestionDao()->updateQuestion($question['id'], QuestionSerialize::serialize($field))
        );
    }

    private function getQuizQuestionDao()
    {
        return $this->createDao('Quiz.QuizQuestionDao');
    }

}

