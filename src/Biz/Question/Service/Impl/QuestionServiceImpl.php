<?php
namespace Biz\Question\Service\Impl;

use Biz\BaseService;
use Topxia\Common\ArrayToolkit;
use Codeages\Biz\Framework\Event\Event;
use Topxia\Service\Common\ServiceKernel;
use Biz\Question\Service\QuestionService;
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

        $fields['createdTime'] = time();
        $fields['updatedTime'] = time();
        $fields                = $questionConfig->filter($fields);

        $question = $this->getQuestionDao()->create($fields);

        if ($question['parentId'] > 0) {
            $this->waveCount($question['parentId'], array('subCount' => '1'));
        }

        // $this->dispatchEvent('question.create', new Event($question, array('argument' => $argument)));

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

        $fields['updatedTime'] = time();
        $fields                = $questionConfig->filter($fields);

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
            $this->waveCount($question['parentId'], array('subCount' => '1'));
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
        $conditions = $this->filterQuestionFields($conditions);
        return $this->getQuestionDao()->search($conditions, $sort, $start, $limit);
    }

    public function searchCount($conditions)
    {
        $conditions = $this->filterQuestionFields($conditions);
        return $this->getQuestionDao()->count($conditions);
    }

    public function getQuestionConfig($type)
    {
        return $this->biz["question_type.{$type}"];
    }

    public function waveCount($id, $diffs)
    {
        return $this->getQuestionDao()->wave(array($id), $diffs);
    }

    public function judgeQuestion($question, $answer)
    {
        if (!$question) {
            return array('status' => 'notFound', 'score' => 0);
        }

        if (!$answer) {
            return array('status' => 'noAnswer', 'score' => 0);
        }

        $questionConfig = $this->getQuestionConfig($question['type']);
        return $questionConfig->judge($question, $answer);
    }

    public function hasEssay($questionIds)
    {
        $count = $this->searchCount(array('ids' => $questionIds, 'type' => 'essay'));

        if ($count) {
            return true;
        }

        return false;
    }

    public function getQuestionCountGroupByTypes($conditions)
    {
        return $this->getQuestionDao()->getQuestionCountGroupByTypes($conditions);
    }

    /**
     * question_favorite
     */

    public function createFavoriteQuestion($fields)
    {
        return $this->getQuestionFavoriteDao()->create($fields);
    }

    public function updateFavoriteQuestion($id, $fields)
    {
        return $this->getQuestionFavoriteDao()->update($id, $fields);
    }

    public function deleteFavoriteQuestion($id)
    {
        return $this->getQuestionFavoriteDao()->delete($id);
    }

    public function searchFavoriteQuestions($conditions, $orderBy, $start, $limit)
    {
        return $this->getQuestionFavoriteDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function searchFavoriteCount($conditions)
    {
        return $this->getQuestionFavoriteDao()->count($conditions);
    }

    public function findUserFavoriteQuestions($userId)
    {
        return $this->getQuestionFavoriteDao()->findUserFavoriteQuestions($userId);
    }

    public function deleteFavoriteByQuestionId($questionId)
    {
        return $this->getQuestionFavoriteDao()->deleteFavoriteByQuestionId($questionId);
    }

    public function filterQuestionFields($conditions)
    {
        if (!empty($conditions['range']) && $conditions['range'] == 'lesson') {
            $conditions['lessonId'] = 0;
        }

        if (empty($conditions['difficulty'])) {
            unset($conditions['difficulty']);
        }

        if (!empty($conditions['keyword'])) {
            $conditions['stem'] = $conditions['keyword'];
            unset($conditions['keyword']);
        }

        if (empty($conditions['type'])) {
            unset($conditions['type']);
        }

        if (!empty($conditions['target'])) {
            $conditions['lessonId'] = $conditions['target'];
            unset($conditions['target']);
        } else {
            unset($conditions['target']);
        }

        if (empty($conditions['excludeIds'])) {
            unset($conditions['excludeIds']);
        } else {
            $conditions['excludeIds'] = explode(',', $conditions['excludeIds']);
        }

        return $conditions;
    }

    public function findAttachments($questionIds)
    {
        if (empty($questionIds)) {
            return array();
        }

        $conditions = array(
            'type'        => 'attachment',
            'targetTypes' => array('question.stem', 'question.analysis'),
            'targetIds'   => $questionIds
        );
        $attachments = $this->getUploadFileService()->searchUseFiles($conditions);
        array_walk($attachments, function (&$attachment) {
            $attachment['dkey'] = $attachment['targetType'].$attachment['targetId'];
        });

        return ArrayToolkit::group($attachments, 'dkey');
    }

    protected function getQuestionDao()
    {
        return $this->createDao('Question:QuestionDao');
    }

    protected function getQuestionFavoriteDao()
    {
        return $this->createDao('Question:QuestionFavoriteDao');
    }

    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File:UploadFileService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
