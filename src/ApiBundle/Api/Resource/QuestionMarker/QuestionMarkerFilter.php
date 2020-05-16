<?php

namespace ApiBundle\Api\Resource\QuestionMarker;

use ApiBundle\Api\Resource\Filter;
use AppBundle\Common\ArrayToolkit;

class QuestionMarkerFilter extends Filter
{
    protected $publicFields = array(
        'id', 'second', 'mediaId', 'questionMarkers',
    );

    protected function publicFields(&$data)
    {
        $data['markerItems'] = $data['questionMarkers'];
        foreach ($data['markerItems'] as &$markerItem) {
            $markerItem = ArrayToolkit::parts($markerItem, array('id', 'item', 'item_report'));
        }
        unset($data['questionMarkers']);
    }
}
