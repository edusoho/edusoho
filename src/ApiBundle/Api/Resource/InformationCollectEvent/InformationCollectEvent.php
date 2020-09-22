<?php

namespace ApiBundle\Api\Resource\InformationCollectEvent;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class InformationCollectEvent extends AbstractResource
{
    public function get(ApiRequest $request, $action)
    {
        $location = [
            'targetType' => $request->query->get('targetType', ''),
            'targetId' => $request->query->get('targetId', '0'),
        ];

        $event = $this->getInformationCollectEventService()->getEventByActionAndLocation($action, $location);
        if (!empty($event)) {
            $event['isSubmited'] = $this->getInformationCollectResultService()->isSubmited($this->getCurrentUser()->getId(), $event['id']);
        }

        return $event ?: (object) [];
    }

    protected function getInformationCollectEventService()
    {
        return $this->service('InformationCollect:EventService');
    }

    protected function getInformationCollectResultService()
    {
        return $this->service('InformationCollect:ResultService');
    }
}
