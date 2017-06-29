<?php

namespace AppBundle\Controller;

use Biz\Event\Service\EventService;
use Symfony\Component\HttpFoundation\Request;

class EventController extends BaseController
{
    public function dispatchAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser->isLogin()) {
            return $this->createJsonResponse('fail');
        }
        $data = $request->request->all();
        $data['userId'] = $currentUser->getId();
        $eventName = $request->request->get('eventName');
        $subjectId = $request->request->get('subjectId');
        $subjectType = $request->request->get('subjectType');

        $subject = $this->getEventService()->getEventSubject($subjectType, $subjectId);

        if (empty($subject)) {
            return $this->createJsonResponse('fail');
        }

        $this->getEventService()->dispatch($eventName, $subject, $data);

        return $this->createJsonResponse($eventName);
    }

    /**
     * @return EventService
     */
    private function getEventService()
    {
        return $this->createService('Event:EventService');
    }
}
