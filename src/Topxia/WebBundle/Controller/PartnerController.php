<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

    public function windidAction(Request $request)
    {
        if (!defined('WEKIT_TIMESTAMP')) {
            define('WEKIT_TIMESTAMP', time());
        }

        require_once __DIR__ . '/../../../../web/windid_client/src/windid/WindidApi.php';//引入windid接口类
        require_once __DIR__ . '/../../../../web/windid_client/src/windid/service/base/WindidUtility.php'; //引入windid工具库

        $windidKey = $request->query->get('windidkey');
        $queryTime = $request->query->get('time');
        $clientId = $request->query->get('clientid');
        $operation = $request->query->get('operation');

        $currentTimestamp = \Pw::getTime();

        if (\WindidUtility::appKey(WINDID_CLIENT_ID, $queryTime, WINDID_CLIENT_KEY, $_GET, $_POST) != $windidKey) {
            return $this->createWindidResponse('sign error.');
        }

        if ($currentTimestamp -> $queryTime >120) {
            return $this->createWindidResponse('timeout.');
        }

        list($method, $args) = $this->getWindidMethod($operation);

        if (!$method) {
            return $this->createWindidResponse('fail');
        }

        $notify = new WindidNotify();  //定义一个通知处理类 在这时定义为下一步所示的notify
        if(!method_exists($notify, $method)) {
            return $this->createWindidResponse('success');
        }

        $result = call_user_func_array(array($notify, $method), $request->request->all());


        if ($result == true) {
            return $this->createWindidResponse('success');
        }

        return $this->createWindidResponse('fail');
    }

    private function getWindidMethod($operation)
    {
        $config = include  __DIR__ . '/../../../../web/windid_client/src/windid/service/base/WindidNotifyConf.php';
        $method = isset($config[$operation]['method']) ? $config[$operation]['method'] : '';
        $args = isset($config[$operation]['args']) ? $config[$operation]['args'] : array();
        return array($method, $args);
    }

    private function createWindidApi($name)
    {
        if (!defined('WEKIT_TIMESTAMP')) {
            define('WEKIT_TIMESTAMP', time());
        }
        require_once __DIR__ .'/../../../../web/windid_client/src/windid/WindidApi.php';
        return \WindidApi::api($name);
    }

    private function createWindidResponse($content = 'success')
    {
        return new Response($content);
    }

}

class WindidNotify
{
    public function test($uid) {
        return $uid ? true : false;
    }
            
    public function addUser($uid) {
        $api = WindidApi::api('user');
        $user = $api->getUser($uid);
            //客户端系统处理添加新用户
         return true;
    }
            
    public function editUser($uid) {
        global $cfg_ml,$dsql;
        $api = WindidApi::api('user');
        $user = $api->getUser($uid);
          //客户端系统处理修改用户信息
        return true;
    }
        
    public function synLogin($uid) {
        return true;
    }
            
    public function synLogout($uid) {
        return true;
    }
}