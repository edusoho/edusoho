<?php

namespace ApiBundle\Api\Resource\InformationCollectForm;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\InformationCollect\FormItem\FormItemFectory;
use Biz\InformationCollect\InformationCollectException;

class InformationCollectForm extends AbstractResource
{
    public function get(ApiRequest $request, $eventId)
    {
        $event = $this->getInformationCollectEventService()->get($eventId);
        if (empty($event)) {
            throw InformationCollectException::NOTFOUND_COLLECTION();
        }

        $event['items'] = $this->getInformationCollectEventService()->findItemsByEventId($eventId);
        $result = $this->getInformationCollectResultService()->getResultByUserIdAndEventId($this->getCurrentUser()->getId(), $eventId);
        if (!empty($result['items'])) {
            $resultItems = ArrayToolkit::index($result['items'], 'code');
            $event['allowSkip'] = true;
        }

        foreach ($event['items'] as &$item) {
            $value = empty($resultItems[$item['code']]) ? '' : $resultItems[$item['code']]['value'];
            $item = FormItemFectory::create($item['code'])->required($item['required'])->value($value)->getData();
        }

        return $event;
    }

    public function add(ApiRequest $request)
    {
        $eventId = $request->request->get('eventId', '');

        $this->getInformationCollectResultService()->submitForm(
            $this->getCurrentUser()->getId(),
            $eventId,
            $request->request->all()
        );

        return $this->get($request, $eventId);
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
