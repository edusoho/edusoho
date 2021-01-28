<?php

namespace Biz\Marker\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Marker\MarkerException;
use Biz\Marker\QuestionMarkerException;
use Biz\Marker\Service\MarkerService;
use Biz\Marker\Service\QuestionMarkerService;
use Codeages\Biz\ItemBank\Item\Service\ItemService;

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
            return [];
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

    public function addQuestionMarker($itemId, $markerId, $seq)
    {
        $item = $this->getItemService()->getItemWithQuestions($itemId, true);

        if (!empty($item['questions'])) {
            $questionMarker = [
                'markerId' => $markerId,
                'questionId' => $itemId,
                'seq' => $seq,
                'createdTime' => time(),
            ];
            $this->findQuestionMarkersByMarkerId($markerId);
            $this->getQuestionMarkerDao()->waveSeqBehind($markerId, $seq);

            return $this->getQuestionMarkerDao()->create($questionMarker);
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
            $this->createNewException(QuestionMarkerException::NOTFOUND_QUESTION_MARKER());
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
            $fields = ['seq' => $seq];

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

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->biz->service('ItemBank:Item:ItemService');
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
