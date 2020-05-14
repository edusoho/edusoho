<?php

namespace ApiBundle\Api\Resource\QuestionMarker;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class QuestionMarker extends AbstractResource
{
    public function get(ApiRequest $request, $mediaId)
    {
        $questionMarkers = $this->getMarkerService()->findMarkersMetaByMediaId($mediaId);

        return $questionMarkers;
    }

    protected function getMarkerService()
    {
        return $this->service('Marker:MarkerService');
    }
}
