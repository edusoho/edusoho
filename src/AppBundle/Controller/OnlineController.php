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
                'lifetime' => 86400,
            );
            $this->getOnlineService()->sample($online);
        }

        return new Response('true');
    }

    public function indexAction(Request $request)
    {
        $conditions = array(
            'gt_access_time' => time() - 15 * 60,
        );

        if ($request->query->get('name', '')) {
            $user = $this->getUserService()->getUserByNickname($request->query->get('name', ''));
            if (empty($user)) {
                return $this->render('admin/online/index.html.twig', array(
                    'onlines' => array(),
                    'paginator' => new Paginator(
                        $this->get('request'),
                        0,
                        20
                    ),
                    'users' => array(),
                ));
            } else {
                $conditions['user_id'] = $user['id'];
            }
        }

        $type = $request->query->get('type', 'online');
        if ($type == 'logined') {
            $conditions['gt_user_id'] = 0;
        }

        $count = $this->getOnlineService()->countOnlines($conditions);
        $paginator = new Paginator(
            $this->get('request'),
            $count,
            20
        );

        $onlines = array();
        if ($count > 0) {
            $onlines = $this->getOnlineService()->searchOnlines(
                $conditions, array(), $paginator->getOffsetCount(), $paginator->getPerPageCount()
            );
        }

        $users = array();
        if (!empty($onlines)) {
            $userIds = ArrayToolkit::column($onlines, 'user_id');
            $users = $this->getUserService()->findUsersByIds($userIds);
            $users = ArrayToolkit::index($users, 'id');
        }

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

    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }
}
