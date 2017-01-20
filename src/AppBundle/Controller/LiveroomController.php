<?php
namespace AppBundle\Controller;

use Biz\CloudPlatform\CloudAPIFactory;
use Symfony\Component\HttpFoundation\Request;

class LiveroomController extends BaseController
{
    public function _entryAction(Request $request, $roomId, $params = array())
    {
        $user           = $request->query->all();
        $user['device'] = $this->getDevice($request);

        if ($request->isSecure()) {
            $user['protocol'] = 'https';
        }

        $systemUser     = $this->getUserService()->getUser($user['id']);
        $avatar         = !empty($systemUser['smallAvatar']) ? $systemUser['smallAvatar'] : '';
        $avatar         = $this->getWebExtension()->getFurl($avatar, 'avatar.png');
        $user['avatar'] = $avatar;

        $ticket = CloudAPIFactory::create('leaf')->post("/liverooms/{$roomId}/tickets", $user);

        return $this->render("liveroom/entry.html.twig", array(
            'roomId' => $roomId,
            'params' => $params,
            'ticket' => $ticket
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
        if ($this->isMobileClient()) {
            return 'mobile';
        } else {
            return 'desktop';
        }
    }

    protected function getWebExtension()
    {
        return $this->container->get('topxia.twig.web_extension');
    }
}
