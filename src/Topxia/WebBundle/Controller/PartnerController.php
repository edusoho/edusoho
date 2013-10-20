<?php
namespace Topxia\WebBundle\Controller;

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

    	$api = $this->createWindidApi('user');

    	$apiUser = $api->getUser($user['email'], 3);
    	if (empty($apiUser)) {
    		return $this->createMessageResponse('error', 'WINDID中不存在该用户！');
    	}

    	$loginScript = $api->synLogin($apiUser['uid']);

    	$goto = $request->query->get('goto') ? : $this->generateUrl('homepage');

    	return $this->render('TopxiaWebBundle:Partner:message.html.twig', array(
            'type' => 'info',
            'title' => '登录成功',
            'message' => '正在跳转页面，请稍等....',
            'duration' => 3000,
            'goto' => $goto,
    		'script' => $loginScript,
		));
	}

	public function logoutAction(Request $request)
	{
        $userId = (int) $request->query->get('userId');

        $user = $this->getUserService()->getUser($userId);
        if (empty($user)) {
            return $this->createMessageResponse('error', '用户不存在，退出登录失败！');
        }

        $api = $this->createWindidApi('user');

        $apiUser = $api->getUser($user['email'], 3);
        if (empty($apiUser)) {
            return $this->createMessageResponse('error', 'WINDID中不存在该用户，同步登出失败！');
        }

        $logoutScript = $api->synLogout($apiUser['uid']);
        $goto = $request->query->get('goto') ? : $this->generateUrl('homepage');

        return $this->render('TopxiaWebBundle:Partner:message.html.twig', array(
            'type' => 'info',
            'title' => '退出成功',
            'message' => '正在跳转页面，请稍等....',
            'duration' => 3000,
            'goto' => $goto,
            'script' => $logoutScript,
        ));

	}

    private function createWindidApi($name)
    {
        if (!defined('WEKIT_TIMESTAMP')) {
            define('WEKIT_TIMESTAMP', time());
        }
        require_once __DIR__ .'/../../../../web/windid_client/src/windid/WindidApi.php';
        return \WindidApi::api($name);
    }

}