<?php
namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

class LiveroomController extends BaseController
{
    public function _entryAction(Request $request, $courseId, $activityId, $roomId)
    {
        $user           = $request->query->all();
        $user['device'] = $this->getDevice($request);

        $ticket = CloudAPIFactory::create('leaf')->post("/liverooms/{$roomId}/tickets", $user);

        return $this->render("activity/live/entry.html.twig", array(
            'courseId'   => $courseId,
            'activityId' => $activityId,
            'roomId'     => $roomId,
            'ticket'     => $ticket
        ));
    }

    public function ticketAction(Request $request, $roomId)
    {
        $ticketNo = $request->query->get('ticket');
        $ticket   = CloudAPIFactory::create('leaf')->get("/liverooms/{$roomId}/tickets/{$ticketNo}");

        return $this->createJsonResponse($ticket);
    }

    protected function getDevice($request)
    {
        // if ($this->isMobileClient()) {
        // return 'mobile';
        // } else {
        return 'desktop';
        // }
    }
}
