<?php

namespace AppBundle\Controller;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\DeviceToolkit;
use AppBundle\Common\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OnlineController extends BaseController
{
    public function sampleAction(Request $request)
    {
        $sessionId = $request->getSession()->getId();
//        $lastFlushTime = $request->getSession()->get('online_flush_time', 0);
        if (!empty($sessionId)) {
            $online = array(
                'sess_id' => $sessionId,
                'ip' => $request->getClientIp(),
                'user_agent' => $request->headers->get('User-Agent', ''),
                'source' => DeviceToolkit::isMobileClient() ? '手机浏览器' : 'PC',
            );
            $this->getOnlineService()->saveOnline($online);
//            $request->getSession()->set('online_flush_time', time());
        }

        return new Response('true');
    }

    public function indexAction(Request $request)
    {
        $conditions = array(
            'active_time_GT' => time() - 15 * 60,
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
            $conditions['is_login'] = 1;
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
                $conditions, array('active_time' => 'DESC'), $paginator->getOffsetCount(), $paginator->getPerPageCount()
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
