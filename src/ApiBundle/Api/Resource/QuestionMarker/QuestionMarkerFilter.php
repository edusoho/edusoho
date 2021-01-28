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
            if (!empty($markerItem['item_report']) && !is_object($markerItem['item_report'])) {
                foreach ($markerItem['item_report']['question_reports'] as &$questionReport) {
                    $questionReport['response'] = $this->convertAbsoluteUrl($questionReport['response']);
                }
            }
            !empty($markerItem['item']['material']) && $markerItem['item']['material'] = $this->convertAbsoluteUrl($markerItem['item']['material']);
            !empty($markerItem['item']['analysis']) && $markerItem['item']['analysis'] = $this->convertAbsoluteUrl($markerItem['item']['analysis']);
            foreach ($markerItem['item']['questions'] as &$question) {
                !empty($question['stem']) && $question['stem'] = $this->convertAbsoluteUrl($question['stem']);
                !empty($question['analysis']) && $question['analysis'] = $this->convertAbsoluteUrl($question['analysis']);
                empty($question['response_points']) && $question['response_points'] = array();
                foreach ($question['response_points'] as &$point) {
                    !empty($point['checkbox']['text']) && $point['checkbox']['text'] = $this->convertAbsoluteUrl($point['checkbox']['text']);
                    !empty($point['radio']['text']) && $point['radio']['text'] = $this->convertAbsoluteUrl($point['radio']['text']);
                }
            }
        }
        unset($data['questionMarkers']);
    }
}
