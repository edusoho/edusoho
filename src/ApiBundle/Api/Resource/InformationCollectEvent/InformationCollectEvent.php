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

        if ('course' == $location['targetType'] && '0' != $location['targetId']) {
            $course = $this->getCourseService()->getCourse($location['targetId']);
            $location['targetId'] = $course['courseSetId'];
        }

        $event = $this->getInformationCollectEventService()->getEventByActionAndLocation($action, $location);
        if (!empty($event)) {
            $event['isSubmited'] = $this->getInformationCollectResultService()->isSubmited($this->getCurrentUser()->getId(), $event['id']);
            $event['allowSkip'] = true === $event['isSubmited'] ? true : (bool) $event['allowSkip'];
        }

        return $event ?: (object) [];
    }

    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
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
