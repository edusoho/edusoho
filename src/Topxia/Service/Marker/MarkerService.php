<?php
namespace Topxia\Service\Marker;

interface MarkerService
{
    public function getMarker($id);

    public function getMarkersByIds($ids);

    public function searchMarkers($conditions, $orderBy, $start, $limit);

    public function updateMarker($id, $fields);

    public function addMarker($mediaId, $fields);

    public function deleteMarker($id);

    public function canManageMarker($lessonUserId);

    public function merge($sourceMarkerId, $targetMarkerId);

}
