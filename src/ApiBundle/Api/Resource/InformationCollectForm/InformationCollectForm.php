<?php

namespace ApiBundle\Api\Resource\InformationCollectForm;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\InformationCollect\FormItem\FormItemFectory;
use Biz\InformationCollect\InformationCollectionException;

class InformationCollectForm extends AbstractResource
{
    public function get(ApiRequest $request, $eventId)
    {
        $event = $this->getInformationCollectEventService()->get($eventId);
        if (empty($event)) {
            throw InformationCollectionException::NOTFOUND_COLLECTION();
        }

        $event['items'] = $this->getInformationCollectEventService()->findItemsByEventId($eventId);
        $result = $this->getInformationCollectResultService()->getResultByUserIdAndEventId($this->getCurrentUser()->getId(), $eventId);
        if (!empty($result['items'])) {
            $resultItems = ArrayToolkit::index($result['items'], 'code');
        }

        foreach ($event['items'] as &$item) {
            $value = empty($resultItems[$item['code']]) ? '' : $resultItems[$item['code']]['value'];
            $item['data'] = FormItemFectory::create($item['code'])->required($item['required'])->value($value)->getData();
        }

        return $event;
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
