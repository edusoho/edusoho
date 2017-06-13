<?php

namespace Biz\Marker\Service\Impl;

use AppBundle\Common\ArrayToolkit;
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

    public function finishQuestionMarker($questionMarkerId, $fields)
    {
        $fields = ArrayToolkit::parts($fields, array('answer', 'userId', 'taskId'));

        $questionMarker = $this->getQuestionMarkerService()->getQuestionMarker($questionMarkerId);

        $questionConfig = $this->getQuestionConfig($questionMarker['type']);

        $questionMarker['score'] = 0;
        $status = $questionConfig->judge($questionMarker, $fields['answer']);

        $fields['status'] = $status['status'];
        $fields['markerId'] = $questionMarker['markerId'];
        $fields['questionMarkerId'] = $questionMarker['id'];

        return $this->addQuestionMarkerResult($fields);
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
