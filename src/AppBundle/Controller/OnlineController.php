<?php

namespace AppBundle\Controller;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OnlineController extends BaseController
{
    public function sampleAction(Request $request)
    {
        $sessionId = $request->getSession()->getId();
        //        $lastFlushTime = $request->getSession()->get('online_flush_time', 0);

        $cookieName = 'online-uuid';

        $uuid = $request->cookies->get($cookieName, $this->generateGuid());

        if (!empty($sessionId)) {
            $this->get('user.online_track')->track($uuid);
        }

        $response = new Response('true');
        $response->headers->setCookie(new Cookie($cookieName, $uuid));

        return $response;
    }

    protected function generateGuid()
    {
        if (function_exists('com_create_guid')) {
            return com_create_guid();
        } else {
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);
            $uuid = substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid, 12, 4).$hyphen
                .substr($charid, 16, 4).$hyphen
                .substr($charid, 20, 12);

            return $uuid;
        }
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
        if ('logined' == $type) {
            $conditions['is_login'] = 1;
        } elseif ('anonymous' == $type) {
            $conditions['is_login'] = 0;
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

    /**
     * @return \Codeages\Biz\Framework\Session\Service\OnlineService
     */
    protected function getOnlineService()
    {
        return $this->createService('Session:OnlineService');
    }

    /**
     * @return \Biz\User\Service\UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
