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
    	define('WEKIT_TIMESTAMP', time());
    	require_once __DIR__ .'/../../../../web/windid_client/src/windid/WindidApi.php';
    	$api = \WindidApi::api('user');

    	// $user['email'] = 'admin@phpwind.com';

    	$user = $api->getUser($user['email'], 3);
    	if (empty($user)) {
    		return $this->createMessageResponse('error', 'WINDID中不存在该用户！');
    	}

    	$loginScript = $api->synLogin($user['uid']);

    	$target = $request->query->get('_target') ? : $this->generateUrl('homepage');

    	return $this->render('TopxiaWebBundle:Partner:message.html.twig', array(
    		'type' => 'login',
    		'script' => $loginScript,
    		'target' => $target,
		));
	}

	public function logoutAction(Request $request)
	{

	}

}