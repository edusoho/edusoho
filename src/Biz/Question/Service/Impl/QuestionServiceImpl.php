<?php
namespace Biz\Question\Service\Impl;

use Biz\BaseService;
use Topxia\Common\ArrayToolkit;
use Biz\Question\Config\QuestionFactory;
use Biz\Question\Service\QuestionService;
use Topxia\Service\Question\Type\QuestionTypeFactory;

class QuestionServiceImpl extends BaseService implements QuestionService
{
    public function get($id)
    {
        return $this->getQuestionDao()->get($id);
    }

    public function create($fields)
    {
        $argument         = $fields;
        $user             = $this->getCurrentuser();
        $fields['userId'] = $user['id'];

        $fields = $this->getQuestionConfig($fields['type'])->filter($fields, 'create');

        $question = $this->getQuestionDao()->create($fields);

        $this->dispatchEvent("question.create", array('argument' => $argument, 'question' => $question));

        return $question;
    }

    public function update($id, $fields)
    {
        return $this->getQuestionDao()->update($id, $fields);
    }

    public function delete($id)
    {
        return $this->getQuestionDao()->delete($id);
    }

    public function findQuestionsByIds(array $ids)
    {
        $questions = $this->getQuestionDao()->findQuestionsByIds($ids);
        return ArrayToolkit::index($questions, 'id');
    }

    public function findQuestionsByParentId($id)
    {
        return $this->getQuestionDao()->findQuestionsByParentId($id);
    }

    public function search($conditions, $sort, $start, $limit)
    {
        return $this->getQuestionDao()->search($conditions, $sort, $start, $limit);
    }

    public function searchCount($conditions)
    {
        return $this->getQuestionDao()->count($conditions);
    }

    public function getQuestionConfig($type)
    {
        return QuestionFactory::create($this->biz, $type);
    }

    public function getQuestionTypes()
    {
        $questions = QuestionFactory::all($this->biz);

        return array_keys($questions);
    }

    public function findCourseTasks($courseId)
    {
        return array();
    }

    public function judgeQuestions(array $answers, $refreshStats = false)
    {
        $questions = $this->findQuestionsByIds(array_keys($answers));

        $results = array();
        foreach ($answers as $id => $answer) {
            if (empty($answer)) {
                $results[$id] = array('status' => 'noAnswer');
            } elseif (empty($questions[$id])) {
                $results[$id] = array('status' => 'notFound');
            } else {
                $question     = $questions[$id];
                $results[$id] = QuestionTypeFactory::create($question['type'])->judge($question, $answer);
            }
        }

        return $results;
    }

    protected function getQuestionDao()
    {
        return $this->createDao('Question:QuestionDao');
    }

    private function getCourseTaskService()
    {
        return $this->createService('Task:TaskService');
    }
}
