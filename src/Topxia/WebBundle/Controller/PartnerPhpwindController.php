<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Topxia\Service\Common\ServiceKernel;

class PartnerPhpwindController extends BaseController
{

    public function notifyAction(Request $request)
    {
        if (!defined('WEKIT_TIMESTAMP')) {
            define('WEKIT_TIMESTAMP', time());
        }

        require_once __DIR__ . '/../../../../vendor_user/windid_client/src/windid/WindidApi.php';//引入windid接口类
        require_once __DIR__ . '/../../../../vendor_user/windid_client/src/windid/service/base/WindidUtility.php'; //引入windid工具库

        $windidKey = $request->query->get('windidkey');
        $queryTime = $request->query->get('time');
        $clientId = $request->query->get('clientid');
        $operation = $request->query->get('operation');

        $currentTimestamp = \Pw::getTime();

        if (in_array($operation, array(111, 112))) {
            unset($_GET['operation']);
        }

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

        $method = 'do' . ucfirst($method);

        if (!method_exists($this, $method)) {
            return $this->createWindidResponse('success');
        }

        $filteredArgs = array();
        foreach ($args as $key) {
            $filteredArgs[$key] = $request->get($key);
        }

        $result = $this->$method($request, $filteredArgs);
        if ($result == true) {
            return $this->createWindidResponse('success');
        }

        return $this->createWindidResponse('fail');
    }

    private function doTest($request, $args)
    {
        return empty($args['testdata']) ? false : true;
    }

    private function doAddUser($request, $args)
    {
        return true;
    }

    private function doSynLogin($request, $args)
    {
        $api = \WindidApi::api('user');
        $partnerUser = $api->getUser($args['uid']);
        if (empty($partnerUser)) {
            return true;
        }

        $bind = $this->getUserService()->getUserBindByTypeAndFromId('phpwind', $partnerUser['uid']);

        if (empty($bind)) {
            $registration = array(
                'nickname' => $partnerUser['username'],
                'email' => $partnerUser['email'],
                'password' => substr(base_convert(sha1(uniqid(mt_rand(), true)), 16, 36),0, 8),
                'createdTime' => $partnerUser['regdate'],
                'createdIp' => $partnerUser['regip'],
                'token' => array('userId' => $partnerUser['uid']),
            );

            $user = $this->getUserService()->register($registration, 'phpwind');
        } else {
            $user = $this->getUserService()->getUser($bind['toId']);
            if (empty($user)) {
                return true;
            }
        }

        $this->authenticateUser($user);

        return true;
    }

    private function doSynLogout($request, $args)
    {
        $this->get('security.context')->setToken(null);
        $this->get('request')->getSession()->invalidate();
        return true;
    }

    /**
     * 需要修改的字段有：email
     * @todo  如果修改密码，则置user_bind表的syncPassword
     */
    private function doEditUser($request, $args)
    {
        // file_put_contents('/tmp/phpwind', json_encode($args). "\n\n", FILE_APPEND);

        if (!empty($args['changepwd'])) {
            return true;
        }

        $api = \WindidApi::api('user');
        $partnerUser = $api->getUser($args['uid']);

        $bind = $this->getUserService()->getUserBindByTypeAndFromId('phpwind', $partnerUser['uid']);
        if (empty($bind)) {
            return true;
        }

        $this->getUserService()->changeEmail($bind['toId'], $partnerUser['email']);

        return true;
    }

    private function doEditUserInfo($request, $args)
    {
        return true;
    }

    private function doUploadAvatar($request, $args)
    {
        return true;
    }

    private function doEditCredit($request, $args)
    {
        return true;
    }

    private function doEditMessageNum($request, $args)
    {
        return true;
    }

    private function doDeleteUser($request, $args)
    {
        return true;
    }

    private function doSetCredits($request, $args)
    {
        return true;
    }

    private function doAlterAvatarUrl($request, $args)
    {
        return true;
    }

    private function getWindidMethod($operation)
    {
        $config = include  __DIR__ . '/../../../../vendor_user/windid_client/src/windid/service/base/WindidNotifyConf.php';
        $method = isset($config[$operation]['method']) ? $config[$operation]['method'] : '';
        $args = isset($config[$operation]['args']) ? $config[$operation]['args'] : array();
        return array($method, $args);
    }

    private function createWindidApi($name)
    {
        if (!defined('WEKIT_TIMESTAMP')) {
            define('WEKIT_TIMESTAMP', time());
        }
        require_once __DIR__ .'/../../../../vendor_user/windid_client/src/windid/WindidApi.php';
        return \WindidApi::api($name);
    }

    private function createWindidResponse($content = 'success')
    {
        return new Response($content);
    }

}