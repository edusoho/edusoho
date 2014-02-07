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

    public function getCategory($id)
    {
        return $this->getCategoryDao()->getCategoryDao($id);
    }

    public function findCategoriesByTarget($target, $start, $limit)
    {
        return $this->getCategoryDao()->findCategoriesByTarget($target, $start, $limit);
    }

    public function createCategory($fields)
    {
        return $this->getCategoryDao()->createCategory($fields);
    }

    public function updateCategory($id, $fields)
    {
        return $this->getCategoryDao()->updateCategory($id, $fields);
    }

    public function deleteCategory($id)
    {
        return $this->getCategoryDao()->deleteCategory($id);
    }

    public function sortCategories($target, array $sortedIds)
    {
        $categories = $this->findCategoriesByTarget($target);
        $categories = ArrayToolkit::index($categories,'id');

        $seq = 1;
        foreach ($sortedIds as $categoryId) {
            if (array_key_exists($categoryId, $categories)) {
                continue;
            }

            if ($seq == $categories[$categoryId]['seq']) {
                continue;
            }

            $fields = array('seq' => $seq);
            $this->getCategoryDao()->updateCategory($categoryId, $fields);
            $seq ++;
        }
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

    private function getQuestionDao()
    {
        return $this->createDao('Question.QuestionDao');
    }

    private function getCategoryDao()
    {
        return $this->createDao('Question.CategoryDao');
    }

}