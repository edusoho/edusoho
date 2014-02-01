<?php
namespace Topxia\Service\Question\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Question\QuestionService;
use Topxia\Common\ArrayToolkit;

class QuestionServiceImpl extends BaseService implements QuestionService
{

    public function getQuestion($id)
    {
        return $this->getQuestionDao()->getQuestion($id);
    }

    public function findQuestionsByIds(array $ids)
    {
        return $this->getQuestionDao()->findQuestionsByIds($ids);
    }

    public function searchQuestions($conditions, $orderBy, $start, $limit)
    {
        return $this->getQuestionDao()->searchQuestions($conditions, $orderBy, $start, $limit);
    }

    public function searchQuestionsCount($conditions)
    {
        return $this->getQuestionDao()->searchQuestionsCount($conditions);
    }

    public function judgeQuestion($id, $answer, $refreshStats = false)
    {

    }

    public function judgeQuestions(array $answers, $refreshStats = false)
    {
        
    }

    private function getQuestionDao()
    {
        return $this->createDao('Question.QuestionDao');
    }

}