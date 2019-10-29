<?php

namespace AppBundle\Controller\AdminV2\User;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Symfony\Component\HttpFoundation\Request;

class OnlineController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = array(
            'active_time_GT' => time() - 15 * 60,
        );

        if ($request->query->get('name', '')) {
            $user = $this->getUserService()->getUserByNickname($request->query->get('name', ''));
            if (empty($user)) {
                return $this->render('admin-v2/user/online/index.html.twig', array(
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

        return $this->render('admin-v2/user/online/index.html.twig', array(
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
