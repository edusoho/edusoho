<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

/**
 * @todo 需要加个token，来防止页面能直接打开
 */
class PartnerController extends BaseController
{

	public function loginAction(Request $request)
	{
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            return $this->createMessageResponse('error', '用户尚未登录！');
        }

    	define('WEKIT_TIMESTAMP', time());
    	require_once __DIR__ .'/../../../../web/windid_client/src/windid/WindidApi.php';
    	$api = \WindidApi::api('user');

    	$apiUser = $api->getUser($user['email'], 3);
    	if (empty($apiUser)) {
    		return $this->createMessageResponse('error', 'WINDID中不存在该用户！');
    	}

    	$loginScript = $api->synLogin($user['id']);

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
        $logoutScript = '';
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

}