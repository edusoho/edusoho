<?php

namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;
use Topxia\Service\Util\PluginUtil;
use Topxia\Service\Util\CloudClientFactory;

use Topxia\Service\CloudPlatform\KeyApplier;
use Topxia\Service\CloudPlatform\Client\CloudAPI;

class AppController extends BaseController
{
    public function indexAction()
    {
    }

    public function oldUpgradeCheckAction()
    {
        return $this->redirect($this->generateUrl('admin_app_upgrades'));
    }

    public function myCloudAction(Request $request)
    {
       $content = $this->getEduCloudService()->getUserGeneral();

        $api = $this->createAPIClient();
        $info = $api->get('/me');

        if (!empty($info['accessKey'])) {
            $settings = $this->getSettingService()->get('storage', array());
            if (empty($settings['cloud_key_applied'])) {
                $settings['cloud_key_applied'] = 1;
                $this->getSettingService()->set('storage', $settings);
            }
            $this->refreshCopyright($info);
        } else {
            $settings = $this->getSettingService()->get('storage', array());
            $settings['cloud_key_applied'] = 0;
            $this->getSettingService()->set('storage', $settings);
        }

        if(isset($info['licenseDomains'])) {
            $info['licenseDomainCount'] = count(explode(';', $info['licenseDomains']));
        }

        $userAgent = 'Open Edusoho App Client 1.0';
        $connectTimeout = 10;
        $timeout = 10;
        $url = "http://open.edusoho.com/api/v1/context/notice";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $connectTimeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_URL, $url );
        $notices = curl_exec($curl);
        curl_close($curl);
        $notices = json_decode($notices, true);


        $currentTime = date('Y-m-d', time());

        $account = '' ;
        $day = '';
        if (isset($content['account']['arrearageDate'])) {
            $account = $content['account'];
            $arrearageDate = $account['arrearageDate'];
            $diffTime = strtotime($currentTime) - strtotime($arrearageDate);
            $day = $diffTime/(60*60*24);
        }
        
        $user = '' ;
        $packageDate = '' ;
        if (isset($content['user']['endDate'])) {
            $user = $content['user'];
            $endDate = $user['endDate'];
            $diffPackageDate = strtotime($currentTime) - strtotime($endDate);
            $packageDate = $diffPackageDate/(60*60*24);
        }

        $storage = '' ;
        $storageDate = '' ;
        if (isset($content['service']['storage'])) {
            $storage = $content['service']['storage'];
            $diffStorageDate = strtotime($currentTime) - strtotime($storage['endMonth']);
            $storageDate = $diffStorageDate/(60*60*24);
        }

        $live = '' ;
        if (isset($content['service']['live'])) {
            $live = $scontent['service']['live'];
        }

        $sms = '' ;
        if (isset($content['service']['sms'])) {
            $sms = $content['service']['sms'];
        }

        if ($info['levelName'] == '商业授权') {
                return $this->render('TopxiaAdminBundle:App:my-cloud.html.twig', array(
                    'content' =>$content,
                    'packageDate' =>$packageDate,
                    'storageDate' =>$storageDate,
                    'day' =>$day,
                    'storage' =>$storage,
                    'live' =>$live,
                    'user' =>$user,
                    'sms' =>$sms,
                    'account' =>$account,
                    "notices"=>$notices,
                    'info' => $info,
                ));
        }else{
                return $this->render('TopxiaAdminBundle:App:cloud.html.twig', array(
                ));
        }


    }

    private function isLocalAddress($address)
    {
        if (in_array($address, array('localhost', '127.0.0.1'))) {
            return true;
        }

        if (strpos($address, '192.168.') === 0) {
            return true;
        }

        if (strpos($address, '10.') === 0) {
            return true;
        }

        return false;
    }

    public function centerAction(Request $request, $postStatus)
    {   
        $apps = $this->getAppService()->getCenterApps();

        if (isset($apps['error'])) {
            return $this->render('TopxiaAdminBundle:App:center.html.twig', array('status' => 'error','type' => $postStatus));
        }

        if (!$apps) {
            return $this->render('TopxiaAdminBundle:App:center.html.twig', array('status' => 'unlink','type' => $postStatus));
        }

        $theme = array();
        $app = array();
        foreach ($apps as $key => $value) {
            if ($value['type'] == 'theme') {
                $theme[] = $value;
            }elseif ($value['type'] == 'app') {
                $app[] = $value;
            }
        }

        $codes = ArrayToolkit::column($apps, 'code');

        $installedApps = $this->getAppService()->findAppsByCodes($codes);

        return $this->render('TopxiaAdminBundle:App:center.html.twig', array(
        'apps' => $apps,
        'theme' => $theme,
        'allApp' => $app,
        'installedApps' => $installedApps,
        'type' => $postStatus,

    ));

    }

    public function centerHiddenAction(Request $request, $postStatus)
    {
        $apps = $this->getAppService()->getCenterApps();

        if (isset($apps['error'])) {
            return $this->render('TopxiaAdminBundle:App:center-hidden.html.twig', array('status' => 'error','type' => $postStatus));
        }

        if (!$apps) {
            return $this->render('TopxiaAdminBundle:App:center-hidden.html.twig', array('status' => 'unlink','type' => $postStatus));
        }
        
        $theme = array();
        $app = array();
        foreach ($apps as $key => $value) {
            if ($value['type'] == 'theme') {
                $theme[] = $value;
            }elseif ($value['type'] == 'app') {
                $app[] = $value;
            }
        }

        $codes = ArrayToolkit::column($apps, 'code');

        $installedApps = $this->getAppService()->findAppsByCodes($codes);

        return $this->render('TopxiaAdminBundle:App:center-hidden.html.twig', array(
        'apps' => $apps,
        'theme' => $theme,
        'allApp' => $app,
        'installedApps' => $installedApps,
        'type' => $postStatus,
        'appTypeChoices' => 'installedApps',
    ));

    }

    public function installedAction(Request $request, $postStatus)
    {   
        $apps = $this->getAppService()->getCenterApps() ? : array();

        $apps = ArrayToolkit::index($apps, 'code');

        $appsInstalled = $this->getAppService()->findApps(0, 100);
        $appsInstalled = ArrayToolkit::index($appsInstalled, 'code');

        foreach ($appsInstalled as $key => $value) {

            $appsInstalled[$key]['installed'] = 1;

        }

        $apps = array_merge($apps, $appsInstalled);
        $theme = array();
        $plugin = array();
        
        foreach ($apps as $key => $value) {

            if ($value['type'] == 'theme') {
                $theme[] = $value;
            }elseif ($value['type'] == 'plugin' || $value['type'] == 'app') {
                $plugin[] = $value;
            }
        }

        return $this->render('TopxiaAdminBundle:App:installed.html.twig', array(
            'apps' => $apps,
            'theme' => $theme,
            'plugin' => $plugin,
            'type' => $postStatus,
        ));
    }

    public function uninstallAction(Request $request)
    {
        $code = $request->get('code');
        $this->getAppService()->uninstallApp($code);

        return $this->createJsonResponse(true);
    }

    public function upgradesAction()
    {
        $apps = $this->getAppService()->checkAppUpgrades();

        if (isset($apps['error'])) {
            return $this->render('TopxiaAdminBundle:App:upgrades.html.twig', array('status' => 'error'));
        }
        $version = $this->getAppService()->getMainVersion();

        return $this->render('TopxiaAdminBundle:App:upgrades.html.twig', array(
            'apps' => $apps,
            'version' => $version,
        ));
    }

    public function upgradesCountAction()
    {
        $apps = $this->getAppService()->checkAppUpgrades();

        return $this->createJsonResponse(count($apps));
    }

    public function logsAction()
    {
        $paginator = new Paginator(
            $this->get('request'),
            $this->getAppService()->findLogCount(),
            30
        );

        $logs = $this->getAppService()->findLogs(
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($logs, 'userId'));

        return $this->render('TopxiaAdminBundle:App:logs.html.twig', array(
            'logs' => $logs,
            'users' => $users,
        ));
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getEduCloudService()
    {
        return $this->getServiceKernel()->createService('EduCloud.EduCloudService');
    }   
}
