<?php
namespace Biz\Question\Service\Impl;

use Biz\BaseService;
use Topxia\Common\ArrayToolkit;
use Codeages\Biz\Framework\Event\Event;
use Biz\Question\Config\QuestionFactory;
use Biz\Question\Service\QuestionService;
use Topxia\Service\Question\Type\QuestionTypeFactory;
use Topxia\Common\Exception\ResourceNotFoundException;

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

        $questionConfig = $this->getQuestionConfig($fields['type']);
        $media          = $questionConfig->create($fields);

        if (!empty($media)) {
            $fields['metas']['mediaId'] = $media['id'];
        }

        $fields = $questionConfig->filter($fields, 'create');

        $question = $this->getQuestionDao()->create($fields);

        if ($question['parentId'] > 0) {
            $this->waveSubCount($question['parentId'], array('subCount' => '1'));
        }

        $this->dispatchEvent('question.create', new Event($question, array('argument' => $argument)));

        return $question;
    }

    public function update($id, $fields)
    {
        $question = $this->get($id);
        $argument = array('question' => $question, 'fields' => $fields);
        if (!$question) {
            throw new ResourceNotFoundException('question', $id);
        }

        $questionConfig = $this->getQuestionConfig($question['type']);
        if (!empty($question['metas']['mediaId'])) {
            $questionConfig->update($question['metas']['mediaId'], $fields);
        }

        $fields = $questionConfig->filter($fields, 'create');

        $question = $this->getQuestionDao()->update($id, $fields);

        $this->dispatchEvent('question.update', new Event($question, array('argument' => $argument)));

        return $question;
    }

    public function delete($id)
    {
        $question = $this->get($id);
        if (!$question) {
            return false;
        }

        $questionConfig = $this->getQuestionConfig($question['type']);
        $questionConfig->delete($question['metas']['mediaId']);

        $result = $this->getQuestionDao()->delete($id);

        if ($question['parentId'] > 0) {
            $this->waveSubCount($question['parentId'], array('subCount' => '1'));
        }

        if ($question['subCount'] > 0) {
            $this->deleteSubQuestions($question['id']);
        }

        $this->dispatchEvent('question.delete', new Event($question));

        return $result;
    }

    public function batchDeletes($ids)
    {
        if (!$ids) {
            return false;
        }

        foreach ($ids ?: array() as $id) {
            $this->delete($id);
        }

        return true;
    }

    public function deleteSubQuestions($parentId)
    {
        return $this->getQuestionDao()->deleteSubQuestions($parentId);
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

    public function waveSubCount($id, $diffs)
    {
        return $this->getQuestionDao()->wave(array($id), $diffs);
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
