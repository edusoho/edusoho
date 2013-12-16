<?php
namespace Topxia\Service\Quiz\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Quiz\QuestionImplementor;
use Topxia\Service\Quiz\Impl\QuestionSerialize;
use Topxia\Common\ArrayToolkit;

define("PATTERN", "/\[\[(.*?)\]\]/");
define("SUBJECT1", "(____)");
define("SUBJECT2", "/\(____\)/");
    
class FillQuestionImplementorImpl extends BaseService implements QuestionImplementor
{
    

	public function getQuestion($question)
    {
        $question = QuestionSerialize::unserialize($question);
    	$a = array_fill(0,count($question['answer']['0']), SUBJECT2);
        $question['stem'] = preg_replace($a, $question['answer']['0'], $question['stem'], 1);
        return $question;
    }

	public function createQuestion($question, $field){
		if (!empty($question['parentId'])){
            $field['parentId'] = (int) trim($question['parentId']);
        }
        preg_match_all(PATTERN, $field['stem'], $answer);
        $field['stem']  = preg_replace(PATTERN, SUBJECT1, $field['stem']);
        if (count($answer['1']) == 0){
            throw $this->createServiceException('该问题没有答案或答案格式不正确！');
        }
        $field['answer'] = $answer;
        return QuestionSerialize::unserialize(
            $this->getQuizQuestionDao()->addQuestion(QuestionSerialize::serialize($field))
        );

	}

    public function updateQuestion($id, $question, $field){
    	preg_match_all(PATTERN, $field['stem'], $answer);
        $field['stem']  = preg_replace(PATTERN, SUBJECT1, $field['stem']);
        if(count($answer['1']) == 0){
            throw $this->createServiceException('该问题没有答案或答案格式不正确！');
        }
        $field['answer'] = $answer;
        return  QuestionSerialize::unserialize(
            $this->getQuizQuestionDao()->updateQuestion($id, QuestionSerialize::serialize($field))
        );
    }

    private function getQuizQuestionDao()
    {
        return $this->createDao('Quiz.QuizQuestionDao');
    }

}
