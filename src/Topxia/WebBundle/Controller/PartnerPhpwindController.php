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

    private function getWindidMethod($operation)
    {
        $config = include  __DIR__ . '/../../../../vendor_user/windid_client/src/windid/service/base/WindidNotifyConf.php';
        $method = isset($config[$operation]['method']) ? $config[$operation]['method'] : '';
        $args = isset($config[$operation]['args']) ? $config[$operation]['args'] : array();
        return array($method, $args);
    }

    private function createWindidResponse($content = 'success')
    {
        return new Response($content);
    }  

}