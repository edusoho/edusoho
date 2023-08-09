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

        if (!in_array($eventName, $this->needEventNameList())) {
            return $this->createJsonResponse('fail');
        }

        $data = $request->request->all();
        $data['userId'] = $currentUser->getId();
        $subjectId = $request->request->get('subjectId');
        $subjectType = $request->request->get('subjectType');

        if (!in_array($subjectType, $this->needSubjectTypeList())) {
            return $this->createJsonResponse('fail');
        }

        $subject = $this->getEventService()->getEventSubject($subjectType, $subjectId);

        if (empty($subject)) {
            return $this->createJsonResponse('fail');
        }

        $this->getEventService()->dispatched($eventName, $subject, $data);

        return $this->createJsonResponse($eventName);
    }

    private function needNotLoginEventList()
    {
        return [
            'task.preview',
        ];
    }

    private function needEventNameList()
    {
        return ['course.view', 'classroom.view', 'task.view'];
    }

    private function needSubjectTypeList()
    {
        return ['course', 'classroom', 'task', 'courseMember'];
    }

    /**
     * @return EventService
     */
    private function getEventService()
    {
        return $this->createService('Event:EventService');
    }
}
