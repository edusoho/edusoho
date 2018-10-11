<?php

namespace Biz\Marker\Service\Impl;

use Biz\BaseService;
use AppBundle\Common\ArrayToolkit;
use Biz\Marker\MarkerException;
use Biz\Marker\Service\MarkerService;
use Biz\Marker\Service\QuestionMarkerService;

class QuestionMarkerServiceImpl extends BaseService implements QuestionMarkerService
{
    public function getQuestionMarker($id)
    {
        return $this->getQuestionMarkerDao()->get($id);
    }

    public function findQuestionMarkersByIds($ids)
    {
        return $this->getQuestionMarkerDao()->findByIds($ids);
    }

    public function findQuestionMarkersByMarkerId($markerId)
    {
        $marker = $this->getMarkerService()->getMarker($markerId);

        if (empty($marker)) {
            $this->createNewException(MarkerException::NOTFOUND_MARKER());
        }

        return $this->getQuestionMarkerDao()->findByMarkerId($markerId);
    }

    public function findQuestionMarkersByMarkerIds($markerIds)
    {
        return $this->getQuestionMarkerDao()->findByMarkerIds($markerIds);
    }

    public function findQuestionMarkersMetaByMediaId($mediaId)
    {
        $markers = $this->getMarkerService()->findMarkersByMediaId($mediaId);

        if (empty($markers)) {
            return array();
        }

        $markersGroups = ArrayToolkit::index($markers, 'id');

        $markerIds = ArrayToolkit::column($markers, 'id');

        $questionMarkers = $this->findQuestionMarkersByMarkerIds($markerIds);

        foreach ($questionMarkers as &$questionMarker) {
            if (!empty($markersGroups[$questionMarker['markerId']])) {
                $questionMarker['mediaId'] = $markersGroups[$questionMarker['markerId']]['mediaId'];
                $questionMarker['second'] = $markersGroups[$questionMarker['markerId']]['second'];
            }
        }

        return $questionMarkers;
    }

    public function findQuestionMarkersByQuestionId($questionId)
    {
        return $this->getQuestionMarkerDao()->findByQuestionId($questionId);
    }

    public function searchQuestionMarkersCount($conditions)
    {
        return $this->getQuestionMarkerDao()->count($conditions);
    }

    public function addQuestionMarker($questionId, $markerId, $seq)
    {
        $question = $this->getQuestionService()->get($questionId);

        if (!empty($question)) {
            $questionMarker = array(
                'markerId' => $markerId,
                'questionId' => $questionId,
                'seq' => $seq,
                'type' => $question['type'],
                'stem' => $question['stem'],
                'answer' => $question['answer'],
                'analysis' => $question['analysis'],
                'metas' => $question['metas'],
                'difficulty' => $question['difficulty'],
                'createdTime' => time(),
            );
            $questionMarkers = $this->findQuestionMarkersByMarkerId($markerId);
            $this->getQuestionMarkerDao()->waveSeqBehind($markerId, $seq);
            $questionmarker = $this->getQuestionMarkerDao()->create($questionMarker);

            return $questionmarker;
        }
    }

    public function updateQuestionMarker($id, $fields)
    {
        return $this->getQuestionMarkerDao()->update($id, $fields);
    }

    public function deleteQuestionMarker($id)
    {
        $questionMarker = $this->getQuestionMarker($id);

        if (empty($questionMarker)) {
            throw $this->createServiceException('Question Not Found');
        }

        $this->getQuestionMarkerDao()->delete($questionMarker['id']);

        $this->getQuestionMarkerDao()->waveSeqForward($questionMarker['markerId'], $questionMarker['seq']);

        $questionmarkers = $this->findQuestionMarkersByMarkerId($questionMarker['markerId']);

        if (empty($questionmarkers)) {
            $this->getMarkerService()->deleteMarker($questionMarker['markerId']);
        }

        $this->getQuestionMarkerResultService()->deleteByQuestionMarkerId($id);

        return true;
    }

    public function sortQuestionMarkers(array $ids)
    {
        $seq = 0;

        foreach ($ids as $itemId) {
            ++$seq;
            $item = $this->getQuestionMarker($itemId);
            $fields = array('seq' => $seq);

            if ($fields['seq'] != $item['seq']) {
                $this->updateQuestionMarker($item['id'], $fields);
            }
        }

        return true;
    }

    public function merge($sourceMarkerId, $targetMarkerId)
    {
        $targetMaxSeq = $this->getQuestionMarkerDao()->getMaxSeqByMarkerId($targetMarkerId);
        $maxSeq = !empty($targetMaxSeq) ? $targetMaxSeq['seq'] : 0;

        return $this->getQuestionMarkerDao()->merge($sourceMarkerId, $targetMarkerId, $maxSeq);
    }

    public function searchQuestionMarkers($conditions, $orderBy, $start, $limit)
    {
        return $this->getQuestionMarkerDao()->search($conditions, $orderBy, $start, $limit);
    }

    protected function getQuestionMarkerDao()
    {
        return $this->createDao('Marker:QuestionMarkerDao');
    }

    protected function getLogService()
    {
        return $this->biz->service('System:LogService');
    }

    protected function getQuestionService()
    {
        return $this->biz->service('Question:QuestionService');
    }

    protected function getQuestionMarkerResultService()
    {
        return $this->biz->service('Marker:QuestionMarkerResultService');
    }

    /**
     * @return MarkerService
     */
    protected function getMarkerService()
    {
        return $this->biz->service('Marker:MarkerService');
    }
}
