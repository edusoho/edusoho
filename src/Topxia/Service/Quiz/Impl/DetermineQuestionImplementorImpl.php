<?php
namespace Topxia\Service\Quiz\Impl;

use Topxia\Service\Quiz\QuestionImplementor;
use Topxia\Service\Quiz\Impl\QuestionSerialize;
use Topxia\Common\ArrayToolkit;

class DetermineQuestionImplementorImpl extends BaseQuestionImplementor implements QuestionImplementor
{
	public function getQuestion($question)
    {
        return QuestionSerialize::unserialize($question);
    }

	public function createQuestion($question)
    {
        if (empty($question['answer'])){
            throw $this->createServiceException('缺少必要字段answer，创建题目失败！');
        }

        $question = $this->filterQuestionFields($question);

        return QuestionSerialize::unserialize(
            $this->getQuizQuestionDao()->addQuestion(QuestionSerialize::serialize($question))
        );
	}

    public function updateQuestion($id, $question){
    	if(empty($question['answer'])){
            throw $this->createServiceException('缺少必要字段answer，创建课程失败！');
        }

        $question = $this->filterQuestionFields($question);

        return QuestionSerialize::unserialize(
            $this->getQuizQuestionDao()->updateQuestion($id, QuestionSerialize::serialize($question))
        );
    }

    private function getQuizQuestionDao()
    {
        return $this->createDao('Quiz.QuizQuestionDao');
    }

}

