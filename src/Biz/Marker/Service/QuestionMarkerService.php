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

    public function addQuestionMarker($questionId, $markerId, $seq);

    public function updateQuestionMarker($id, $fields);

    /**
     * @param $id
     *
     * @return mixed
     * @Log(level="info",module="marker",action="delete_question",message="删除驻点问题",targetType="question_marker",format="{'before':{ 'className':'Marker:QuestionMarkerService','funcName':'getQuestionMarker','param':['id']}}")
     */
    public function deleteQuestionMarker($id);

    public function sortQuestionMarkers(array $ids);

    public function searchQuestionMarkers($conditions, $orderBy, $start, $limit);

    public function merge($sourceMarkerId, $targetMarkerId);

    public function searchQuestionMarkersCount($conditions);
}
