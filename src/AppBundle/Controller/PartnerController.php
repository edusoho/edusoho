<?php

namespace AppBundle\Controller;

use Biz\User\Service\AuthService;
use Symfony\Component\HttpFoundation\Request;

/**
 * @todo 需要加个sign，来防止页面能直接打开
 */
class PartnerController extends BaseController
{
    public function loginAction(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            return $this->createMessageResponse('error', '用户尚未登录！');
        }

        $loginScript = $this->getAuthService()->syncLogin($user['id']);

        $goto = $request->query->get('goto') ?: $this->generateUrl('homepage');

        $response = $this->render('partner/message.html.twig', array(
            'type' => 'info',
            'title' => '登录成功',
            'message' => '正在跳转页面，请稍等....',
            'duration' => 3000,
            'goto' => $goto,
            'script' => $loginScript,
        ));

        return $response;
    }

    public function logoutAction(Request $request)
    {
        $userId = (int) $request->query->get('userId');

        $user = $this->getUserService()->getUser($userId);
        if (empty($user)) {
            return $this->createMessageResponse('error', '用户不存在，退出登录失败！');
        }

        $logoutScript = $this->getAuthService()->syncLogout($user['id']);

        $goto = $request->query->get('goto') ?: $this->generateUrl('homepage');

        return $this->render('partner/message.html.twig', array(
            'type' => 'info',
            'title' => '退出成功',
            'message' => '正在跳转页面，请稍等....',
            'duration' => 3000,
            'goto' => $goto,
            'script' => $logoutScript,
        ));
    }

    /**
     * @return AuthService
     */
    protected function getAuthService()
    {
        return $this->getBiz()->service('User:AuthService');
    }
}
