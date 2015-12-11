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

    protected function getQuestionMarkerResultDao()
    {
        return $this->createDao('Marker.QuestionMarkerResultDao');
    }

}
