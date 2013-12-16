<?php
namespace Topxia\Service\Quiz\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Quiz\QuestionImplementor;
use Topxia\Service\Quiz\Impl\QuestionSerialize;
use Topxia\Common\ArrayToolkit;

class DetermineQuestionImplementorImpl extends BaseService implements QuestionImplementor
{
	public function getQuestion($question)
    {
        return QuestionSerialize::unserialize($question);
    }

	public function createQuestion($question, $field){
		if (!empty($question['parentId'])){
            $field['parentId'] = (int) trim($question['parentId']);
        }
        if (empty($question['answers'])){
            throw $this->createServiceException('缺少必要字段,answers，创建课程失败！');
        }
        $field['answer'] = $question['answers'];
        return QuestionSerialize::unserialize(
            $this->getQuizQuestionDao()->addQuestion(QuestionSerialize::serialize($field))
        );
	}

    public function updateQuestion($id, $question, $field){
    	if(empty($question['answers'])){
            throw $this->createServiceException('缺少必要字段,answers，创建课程失败！');
        }
        $field['answer'] = $question['answers'];
        return QuestionSerialize::unserialize(
            $this->getQuizQuestionDao()->updateQuestion($id, QuestionSerialize::serialize($field))
        );
    }

    private function getQuizQuestionDao()
    {
        return $this->createDao('Quiz.QuizQuestionDao');
    }

}

