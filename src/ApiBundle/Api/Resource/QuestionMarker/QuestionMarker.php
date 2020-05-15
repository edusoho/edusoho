<?php

namespace ApiBundle\Api\Resource\QuestionMarker;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class QuestionMarker extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $questionMarkers = $this->getMarkerService()->findMarkersMetaByMediaId($request->query->get('mediaId'));
        
        return $questionMarkers;
    }

    public function add(ApiRequest $request)
    {

    }

    protected function getMarkerService()
    {
        return $this->service('Marker:MarkerService');
    }
}
