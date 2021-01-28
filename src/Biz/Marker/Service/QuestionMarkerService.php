<?php

namespace Biz\Marker\Service;

use Biz\System\Annotation\Log;

interface QuestionMarkerService
{
    public function getQuestionMarker($id);

    public function findQuestionMarkersByIds($ids);

    public function findQuestionMarkersByMarkerId($markerId);

    public function findQuestionMarkersByMarkerIds($markerIds);

    public function findQuestionMarkersMetaByMediaId($mediaId);

    public function findQuestionMarkersByQuestionId($questionId);

    public function addQuestionMarker($itemId, $markerId, $seq);

    public function updateQuestionMarker($id, $fields);

    /**
     * @param $id
     *
     * @return mixed
     * @Log(module="marker",action="delete_question")
     */
    public function deleteQuestionMarker($id);

    public function sortQuestionMarkers(array $ids);

    public function searchQuestionMarkers($conditions, $orderBy, $start, $limit);

    public function merge($sourceMarkerId, $targetMarkerId);

    public function searchQuestionMarkersCount($conditions);
}
