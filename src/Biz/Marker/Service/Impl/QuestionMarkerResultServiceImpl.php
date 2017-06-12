<?php

namespace Biz\Marker\Service\Impl;

use Biz\BaseService;
use Biz\Marker\Dao\QuestionMarkerResultDao;
use Biz\Marker\Service\QuestionMarkerResultService;
use Biz\Marker\Service\QuestionMarkerService;

class QuestionMarkerResultServiceImpl extends BaseService implements QuestionMarkerResultService
{
    public function getQuestionMarkerResult($id)
    {
        return $this->getQuestionMarkerResultDao()->get($id);
    }

    public function addQuestionMarkerResult($result)
    {
        return $this->getQuestionMarkerResultDao()->create($result);
    }

    public function updateQuestionMarkerResult($id, $result)
    {
        return $this->getQuestionMarkerResultDao()->update($id, $result);
    }

    public function finishCurrentQuestion($userId, $questionMarkerId, $answer)
    {
        $questionMarker = $this->getQuestionMarkerService()->getQuestionMarker($questionMarkerId);

        $questionConfig = $this->getQuestionConfig($questionMarker['type']);

        $status =  $questionConfig->judge($questionMarker, $answer);

        return $this->addQuestionMarkerResult(array(
            'markerId' => $questionMarker['markerId'],
            'questionMarkerId' => $questionMarker['id'],
            'userId' => $userId,
            'status' => $status['status'],
            'answer' => $answer,
        ));
    }

    public function deleteByQuestionMarkerId($questionMarkerId)
    {
        return $this->getQuestionMarkerResultDao()->deleteByQuestionMarkerId($questionMarkerId);
    }

    public function findByUserIdAndMarkerId($userId, $markerId)
    {
        return $this->getQuestionMarkerResultDao()->findByUserIdAndMarkerId($userId, $markerId);
    }

    public function findByUserIdAndQuestionMarkerId($userId, $questionMarkerId)
    {
        return $this->getQuestionMarkerResultDao()->findByUserIdAndQuestionMarkerId($userId, $questionMarkerId);
    }

    public function findByTaskIdAndQuestionMarkerId($taskId, $questionMarkerId)
    {
        return $this->getQuestionMarkerResultDao()->findByTaskIdAndQuestionMarkerId($taskId, $questionMarkerId);
    }

    /**
     * @return QuestionMarkerResultDao
     */
    protected function getQuestionMarkerResultDao()
    {
        return $this->createDao('Marker:QuestionMarkerResultDao');
    }

    protected function getQuestionConfig($type)
    {
        return $this->biz["question_type.{$type}"];
    }

    /**
     * @return QuestionMarkerService
     */
    protected function getQuestionMarkerService()
    {
        return $this->biz->service('Marker:QuestionMarkerService');
    }
}
