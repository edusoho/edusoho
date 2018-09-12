<?php

namespace Biz\Marker\Service;

use Biz\System\Annotation\Log;

interface MarkerService
{
    public function getMarker($id);

    public function getMarkersByIds($ids);

    public function findMarkersByMediaId($mediaId);

    public function findMarkersMetaByMediaId($mediaId);

    public function searchMarkers($conditions, $orderBy, $start, $limit);

    public function updateMarker($id, $fields);

    /**
     * @param $fields
     *
     * @return mixed
     * @Log(module="marker",action="create")
     */
    public function addMarker($mediaId, $fields);

    /**
     * @param $id
     *
     * @return mixed
     */
    public function deleteMarker($id);

    public function canManageMarker($lessonUserId);

    public function merge($sourceMarkerId, $targetMarkerId);
}
