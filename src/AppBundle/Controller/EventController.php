<?php

namespace AppBundle\Controller;

use Biz\Event\Service\EventService;
use Symfony\Component\HttpFoundation\Request;

class EventController extends BaseController
{
    public function dispatchAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        $eventName = $request->request->get('eventName');
        if (!$currentUser->isLogin() && !in_array($eventName, $this->needNotLoginEventList())) {
            return $this->createJsonResponse('fail');
        }
        $data = $request->request->all();
        $data['userId'] = $currentUser->getId();
        $subjectId = $request->request->get('subjectId');
        $subjectType = $request->request->get('subjectType');

        $subject = $this->getEventService()->getEventSubject($subjectType, $subjectId);

        if (empty($subject)) {
            return $this->createJsonResponse('fail');
        }

        $this->getEventService()->dispatched($eventName, $subject, $data);

        return $this->createJsonResponse($eventName);
    }

    private function needNotLoginEventList()
    {
        return array(
            'task.preview',
        );
    }

    /**
     * @return EventService
     */
    private function getEventService()
    {
        return $this->createService('Event:EventService');
    }
}
