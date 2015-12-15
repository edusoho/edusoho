<?php

namespace Topxia\Service\Marker\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Marker\QuestionMarkerService;

class QuestionMarkerServiceImpl extends BaseService implements QuestionMarkerService
{
    public function getQuestionMarker($id)
    {
        return $this->getQuestionMarkerDao()->getQuestionMarker($id);
    }

    public function findQuestionMarkersByIds($ids)
    {
        return $this->getQuestionMarkerDao()->findQuestionMarkersByIds($ids);
    }

    public function findQuestionMarkersByMarkerId($markerId)
    {
        return $this->getQuestionMarkerDao()->findQuestionMarkersByMarkerId($markerId);
    }

    public function findQuestionMarkersByQuestionId($questionId)
    {
        return $this->getQuestionMarkerDao()->findQuestionMarkersByQuestionId($questionId);
    }

    public function addQuestionMarker($questionId, $markerId, $seq)
    {
        $question = $this->getQuestionService()->getQuestion($questionId);

        if (!empty($question)) {
            $questionMarker = array(
                'markerId'    => $markerId,
                'questionId'  => $questionId,
                'seq'         => $seq,
                'type'        => $question['type'],
                'stem'        => $question['stem'],
                'answer'      => $question['answer'],
                'analysis'    => $question['analysis'],
                'metas'       => $question['metas'],
                'difficulty'  => $question['difficulty'],
                'createdTime' => time()

            );
            $questionMarkers = $this->findQuestionMarkersByMarkerId($markerId);
            $this->getQuestionMarkerDao()->updateQuestionMarkersSeq($markerId, $seq);
            return $this->getQuestionMarkerDao()->addQuestionMarker($questionMarker);
        }
    }

    public function updateQuestionMarker($id, $fields)
    {
        return $this->getQuestionMarkerDao()->updateQuestionMarker($id, $fields);
    }

    public function deleteQuestionMarker($id)
    {
        $questionMarker = $this->getQuestionMarker($id);
        $this->getQuestionMarkerDao()->deleteQuestionMarker($id);
        $questionMarkerLog = "删除驻点问题\"{$questionMarker['stem']}\"";
        $this->getLogService()->info('questionMarker', 'delete', $questionMarkerLog);
        return true;
    }

    public function sortQuestionMarkers(array $ids)
    {
        $seq = 0;

        foreach ($ids as $itemId) {
            $seq++;
            $item   = $this->getQuestionMarker($itemId);
            $fields = array('seq' => $seq);

            if ($fields['seq'] != $item['seq']) {
                $this->updateQuestionMarker($item['id'], $fields);
            }
        }

        return true;
    }

    public function searchQuestionMarkers($conditions, $orderBy, $start, $limit)
    {
        return $this->getQuestionMarkerDao()->searchQuestionMarkers($conditions, $orderBy, $start, $limit);
    }

    protected function getQuestionMarkerDao()
    {
        return $this->createDao('Marker.QuestionMarkerDao');
    }

    protected function getLogService()
    {
        return $this->createService('System.LogService');
    }

    protected function getQuestionService()
    {
        return $this->createService('Question.QuestionService');
    }
}
