<?php
namespace Topxia\Service\Marker\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Marker\QuestionMarkerResultService;

class QuestionMarkerResultServiceImpl extends BaseService implements QuestionMarkerResultService
{
    public function getQuestionMarkerResult($id)
    {
        return $this->getQuestionMarkerResultDao()->getQuestionMarkerResult($id);
    }

    public function addQuestionMarkerResult($result)
    {
        $result['createdTime'] = time();

        return $this->getQuestionMarkerResultDao()->addQuestionMarkerResult($result);
    }

    public function updateQuestionMarkerResult($id, $result)
    {
        $result['updatedTime'] = time();

        return $this->getQuestionMarkerResultDao()->updateQuestionMarkerResult($id, $result);
    }

    public function deleteByQuestionMarkerId($questionMarkerId)
    {
        return $this->getQuestionMarkerResultDao()->deleteByQuestionMarkerId($questionMarkerId);
    }

    public function findByUserIdAndMarkerId($userId, $markerId)
    {
        return $this->getQuestionMarkerResultDao()->findByUserIdAndMarkerId($userId, $markerId);
    }

    public function findByUserIdAndPluckId($userId, $pluckId)
    {
        return $this->getQuestionMarkerResultDao()->findByUserIdAndPluckId($userId, $pluckId);
    }

    protected function getQuestionMarkerResultDao()
    {
        return $this->createDao('Marker.QuestionMarkerResultDao');
    }

}
