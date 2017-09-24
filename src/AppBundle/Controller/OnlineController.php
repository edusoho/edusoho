<?php

namespace AppBundle\Controller;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OnlineController extends BaseController
{
    public function sampleAction(Request $request)
    {
        if (!empty($request->getSession()->getId())) {
            $online = array(
                'sess_id' => $request->getSession()->getId(),
                'user_id' => $this->getUser()->getId(),
                'ip' => $request->getClientIp(),
                'user_agent' => $request->headers->get('User-Agent', ''),
                'access_url' => $request->headers->get('Referer', ''),
                'source' => 'web',
            );
            $this->getOnlineService()->sample($online);
        }
        return new Response('true');
    }

    public function indexAction(Request $request)
    {
        $conditions = array(
            'lt_access_time' => 15 * 60
        );

        $type = $request->query->get('type', 'online');
        if($type == 'logined') {
            $conditions['gt_user_id'] = 0;
        }

        $paginator = new Paginator(
            $this->get('request'),
            $this->getOnlineService()->countOnlines($conditions),
            20
        );

        $onlines = $this->getOnlineService()->searchOnlines(
            $conditions, array(), $paginator->getOffsetCount(), $paginator->getPerPageCount()
        );

        $userIds = ArrayToolkit::column($onlines, 'user_id');
        $users = $this->getUserService()->findUsersByIds($userIds);
        $users = ArrayToolkit::index($users, 'id');

        return $this->render('admin/online/index.html.twig', array(
            'onlines' => $onlines,
            'paginator' => $paginator,
            'users' => $users,
        ));
    }

    protected function getOnlineService()
    {
        return $this->getBiz()->service('Session:OnlineService');
    }
}
