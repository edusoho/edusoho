<?php
namespace Topxia\Service\Quiz\Impl;

use Topxia\Service\Quiz\QuestionImplementor;
use Topxia\Service\Quiz\Impl\QuestionSerialize;
use Topxia\Common\ArrayToolkit;

class FillQuestionImplementorImpl extends BaseQuestionImplementor implements QuestionImplementor
{
	public function getQuestion($question)
    {
        return QuestionSerialize::unserialize($question);
    }

	public function createQuestion($question) {
        $question = $this->filterQuestionFields($question);

        return QuestionSerialize::unserialize(
            $this->getQuizQuestionDao()->addQuestion(QuestionSerialize::serialize($question))
        );
	}

    public function updateQuestion($id, $question){
        $question = $this->filterQuestionFields($question);

        return  QuestionSerialize::unserialize(
            $this->getQuizQuestionDao()->updateQuestion($id, QuestionSerialize::serialize($question))
        );
    }

    private function getQuizQuestionDao()
    {
        return $this->createDao('Quiz.QuizQuestionDao');
    }

    protected function filterQuestionFields($question)
    {
        $question = parent::filterQuestionFields($question);
        
        preg_match_all("/\[\[(.+?)\]\]/", $question['stem'], $answer, PREG_PATTERN_ORDER);
        if (empty($answer[1])){
            throw $this->createServiceException('该问题没有答案或答案格式不正确！');
        }

        $question['answer'] = array();
        foreach ($answer[1] as $value) {
            $value = explode('|', $value);
            foreach ($value as $i => $v) {
                $value[$i] = trim($v);
            }
            $question['answer'][] = $value;
        }

        return $question;
    }

}
