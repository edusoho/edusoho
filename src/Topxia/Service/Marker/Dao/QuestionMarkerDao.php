<?php

namespace Topxia\Service\Marker\Dao;

interface QuestionMarkerDao
{
    public function getQuestionMarker($id);

    public function getMaxSeqByMarkerId($id);

    public function merge($sourceMarkerId, $targetMarkerId, $maxSeq);

    public function findQuestionMarkersByIds($ids);

    public function findQuestionMarkersByMarkerId($markerId);

    public function findQuestionMarkersByMarkerIds($markerIdss);

    public function findQuestionMarkersByQuestionId($questionId);

    public function addQuestionMarker($questionMarker);

    public function updateQuestionMarker($id, $fields);

    public function deleteQuestionMarker($id);

    public function updateQuestionMarkersSeqBehind($markerId, $seq);

    public function updateQuestionMarkersSeqForward($markerId, $seq);

    public function searchQuestionMarkers($conditions, $orderBy, $start, $limit);

    public function searchQuestionMarkersCount($conditions);
}
