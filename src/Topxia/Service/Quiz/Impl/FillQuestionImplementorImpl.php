<?php
namespace Topxia\Service\Quiz\Impl;

use Topxia\Service\Quiz\QuestionImplementor;
use Topxia\Service\Quiz\Impl\QuestionSerialize;
use Topxia\Common\ArrayToolkit;

const PATTERN = "/\[\[(.+?)\]\]/";
const SUBJECT1 = "(____)";
const SUBJECT2 = "/\(____\)/";
    
class FillQuestionImplementorImpl extends BaseQuestionImplementor implements QuestionImplementor
{
	public function getQuestion($question)
    {
        $question = QuestionSerialize::unserialize($question);
        foreach ($question['answer'] as $key => $value) {
            $value = implode('|', $value);
            $question['stem'] = preg_replace(SUBJECT2, "[[".$value."]]", $question['stem'], 1);
        }
        return $question;
    }

	public function createQuestion($question) {
        $question = $this->filterQuestionFields($question);

        preg_match_all(PATTERN, $question['stem'], $answer, PREG_PATTERN_ORDER);
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

        return QuestionSerialize::unserialize(
            $this->getQuizQuestionDao()->addQuestion(QuestionSerialize::serialize($question))
        );

	}

    public function updateQuestion($id, $question, $field){
    	preg_match_all(PATTERN, $field['stem'], $answer);
        $field['stem']  = preg_replace(PATTERN, SUBJECT1, $field['stem']);
        if(count($answer['1']) == 0){
            throw $this->createServiceException('该问题没有答案或答案格式不正确！');
        }

        foreach ($answer['1'] as $key => $value) {
            $answer['1'][$key] = array_map(function($v){
                return trim($v);
            }, explode('|', $value));
        }

        $field['answer'] = $answer[1];
        return  QuestionSerialize::unserialize(
            $this->getQuizQuestionDao()->updateQuestion($id, QuestionSerialize::serialize($field))
        );
    }

    private function getQuizQuestionDao()
    {
        return $this->createDao('Quiz.QuizQuestionDao');
    }

}
