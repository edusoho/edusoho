<?php
namespace Topxia\Service\Question\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Question\QuestionService;
use Topxia\Common\ArrayToolkit;

class QuestionServiceImpl extends BaseService implements QuestionService
{
    protected $supportedQuestionTypes = array('choice','single_choice', 'fill', 'material', 'essay', 'determine');

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

    public function createQuestion($fields)
    {
        if (!in_array($fields['type'], $this->supportedQuestionTypes)) {
                throw $this->createServiceException('question type error！');
        }

        $filter = $this->createQuestionFilter($fields['type']);
        $fields = $filter->filter($fields, 'create'); 

        return $this->getQuestionDao()->addQuestion($fields);
    }

    public function updateQuestion($id, $fields)
    {
        $question = $this->getQuestion($id);
        if (empty($question)) {
            throw $this->createServiceException("Question #{$id} is not exist.");
        }

        $filter = $this->createQuestionFilter($question['type']);
        $fields = $filter->filter($fields, 'create'); 

        return $this->getQuestionDao()->updateQuestion($id, $fields);
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

    protected function createQuestionFilter($type)
    {
        switch ($type) {
            case 'choice':
            case 'single_choice':
                $name = 'Choice';
                break;
            case 'fill':
                $name = 'Fill';
                break;
            default:
                $name = 'Default';
                break;
        }
        $class = __NAMESPACE__  . "\\Filter\\{$name}Filter";
        return new $class();
    }

}