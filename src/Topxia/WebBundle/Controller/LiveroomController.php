<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

class LiveroomController extends BaseController
{
    public function _entryAction(Request $request, $id)
    {
        $user           = $request->query->all();
        $user['device'] = $this->getDevice($request);
        if ($request->isSecure()) {
            $user['protocol'] = 'https';
        }

        $systemUser = $this->getUserService()->getUser($user['id']);
        $avatar = !empty($systemUser['smallAvatar']) ? $systemUser['smallAvatar'] : '';
        $avatar = $this->getWebExtension()->getFurl($avatar, 'avatar.png');
        $user['avatar'] = $avatar;

        $ticket = CloudAPIFactory::create('leaf')->post("/liverooms/{$id}/tickets", $user);
        return $this->render("TopxiaWebBundle:Liveroom:entry.html.twig", array(
            'roomId' => $id,
            'ticket' => $ticket
        ));
    }

    public function ticketAction(Request $request, $id)
    {
        $ticketNo = $request->query->get('ticket');
        $ticket   = CloudAPIFactory::create('leaf')->get("/liverooms/{$id}/tickets/{$ticketNo}");

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
