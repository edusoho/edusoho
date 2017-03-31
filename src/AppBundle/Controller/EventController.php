<?php

namespace AppBundle\Controller;

use Biz\Event\Service\EventService;
use Symfony\Component\HttpFoundation\Request;

class EventController extends BaseController
{
    public function dispatchAction(Request $request)
    {
        $data        = $request->request->all();
        $eventName   = $request->request->get('eventName');
        $subjectId   = $request->request->get('subjectId');
        $subjectType = $request->request->get('subjectType');

        $subject     = $this->getEventService()->getEventSubject($subjectType, $subjectId);

        if (!empty($subject)) {
            $this->getEventService()->dispatch($eventName, $subject, $data);
        }

        return $this->createJsonResponse('');
    }

    /**
     * @return EventService
     */
    private function getEventService()
    {
        return $this->createService('Event:EventService');
    }
}