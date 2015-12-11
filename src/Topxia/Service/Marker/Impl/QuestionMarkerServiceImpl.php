<?php

namespace Topxia\Service\Marker\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Question\QuestionMarkerService;

class QuestionMarkerServiceImpl extends BaseService implements QuestionMarkerService
{
    public function getQuestionMarker($id)
    {
        return $this->getQuestionMarkerDao()->getQuestionMarker($id);
    }

    public function findQuestionMarkersByIds($ids)
    {
        return $this->findQuestionMarkersByIds($ids);
    }

    public function findQuestionMarkersByMarkerId($markerId)
    {
        return $this->getQuestionMarkerDao()->findQuestionMarkersByMarkerId($markerId);
    }

    public function findQuestionMarkersByQuestionId($questionId)
    {
        return $this->getQuestionMarkerDao()->findQuestionMarkersByQuestionId($questionId);
    }

    public function addQuestionMarker($questionMarker)
    {
        return $this->getQuestionMarkerDao()->addQuestionMarker($questionMarker);
    }

    public function updateQuestionMarker($id, $fields)
    {
        return $this->getQuestionMarkerDao()->updateQuestionMarker($id, $fields);
    }

    public function deleteQuestionMarker($id)
    {
        $questionMarker = $this->getQuestionMarker($id);
        $this->getCourseDao()->deleteCourse($id);
        $questionMarkerLog = "删除驻点问题\"{$questionMarker['stem']}\"";
        $this->getLogService()->info('questionMarker', 'delete', $courseLog);
        return true;
    }

    public function searchQuestionMarkers($conditions, $orderBy, $start, $limit)
    {
        return $this->searchQuestionMarkers($conditions, $orderBy, $start, $limit);
    }

    protected function getQuestionMarkerDao()
    {
        return $this->createDao('Marker.QuestionMarker');
    }

    protected function getLogService()
    {
        return $this->createService('System.LogService');
    }
}
