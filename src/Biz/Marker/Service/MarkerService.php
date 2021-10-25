<?php

namespace Biz\Marker\Service;

use Biz\System\Annotation\Log;

interface MarkerService
{
    public function getMarker($id);

    public function getMarkersByIds($ids);

    public function findMarkersByActivityId($activityId);

    public function findMarkersMetaByActivityId($activityId);

    public function searchMarkers($conditions, $orderBy, $start, $limit);

    public function updateMarker($id, $fields);

    /**
     * @param $activityId
     * @param $fields
     *
     * @return mixed
     * @Log(module="marker",action="create")
     */
    public function addMarker($activityId, $fields);

    /**
     * @param $id
     *
     * @return mixed
     */
    public function deleteMarker($id);

    public function canManageMarker($lessonUserId);

    public function merge($sourceMarkerId, $targetMarkerId);
}
