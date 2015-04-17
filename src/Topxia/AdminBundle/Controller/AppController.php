<?php

namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;
use Topxia\Service\Util\PluginUtil;
use Topxia\Service\Util\CloudClientFactory;

use Topxia\Service\CloudPlatform\KeyApplier;
use Topxia\Service\CloudPlatform\Client\CloudAPI;
use Topxia\Service\CloudPlatform\Client\EduSohoOpenClient;

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
        $content = $this->getEduCloudService()->getUserOverview();
        $info = $this->getEduCloudService()->getAccountInfo();

        $EduSohoOpenClient = new EduSohoOpenClient;
        if (empty($info['level']) or (!(isset($content['service']['storage'])) and !(isset($content['service']['live'])) and !(isset($content['service']['sms'])) )  ) {
            $articles = $EduSohoOpenClient->getArticles();
            $articles = json_decode($articles, true);
            return $this->render('TopxiaAdminBundle:App:cloud.html.twig', array(
                'articles' => $articles,
            ));
        }

        return $this->redirect($this->getUrl("admin_cloud_service_information"));
    }

    public function serviceInformationAction(Request $request)
    {
        $content = $this->getEduCloudService()->getUserOverview();
        $info = $this->getEduCloudService()->getAccountInfo();
        $isBinded = $this->getAppService()->getBinded();

        $EduSohoOpenClient = new EduSohoOpenClient;

        $currentTime = date('Y-m-d', time());

        $account = isset($content['account']) ? $content['account'] : null;
        $day = isset($content['account']['arrearageDate']) ? (strtotime($currentTime) - strtotime($content['account']['arrearageDate']))/(60*60*24) : '';

        $user = isset($content['user']) ? $content['user'] : null ;
        $packageDate = isset($content['user']['endDate']) ? (strtotime($currentTime) - strtotime($content['user']['endDate']))/(60*60*24) : '' ;

        $storage = isset($content['service']['storage']) ? $content['service']['storage'] : null ;
        $storageDate = isset($content['service']['storage']['endMonth']) ? (strtotime($currentTime) - strtotime($content['service']['storage']['endMonth']))/(60*60*24) : '' ;
        $month = isset($content['service']['storage']['bill']['date']) ? substr($content['service']['storage']['bill']['date']) : '' ;

        $live = isset($content['service']['live']) ? $content['service']['live'] : null ;
        $liveDate = isset($content['service']['live']['expire']) ? (strtotime($currentTime) - strtotime($content['service']['live']['expire']))/(60*60*24) : '' ;

        $sms = isset($content['service']['sms']) ? $content['service']['sms'] : null ;

        $notices = $EduSohoOpenClient->getNotices();
        $notices = json_decode($notices, true);

        return $this->render('TopxiaAdminBundle:App:my-cloud.html.twig', array(
            'content' =>$content,
            'packageDate' =>$packageDate,
            'storageDate' =>$storageDate,
            'liveDate' =>$liveDate,
            'day' =>$day,
            'month' => $month,
            'storage' =>$storage,
            'live' =>$live,
            'user' =>$user,
            'sms' =>$sms,
            'account' =>$account,
            "notices"=>$notices,
            'info' => $info,
            'isBinded' => $isBinded,
        ));
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

        $dir = dirname(dirname(dirname(dirname(__DIR__)))); 
        $appMeta = array();

        foreach ($appsInstalled as $key => $value) {
            $appsInstalled[$key]['installed'] = 1;

            if ($key != 'MAIN') {
                $dic = $dir.'/plugins/'.$key.'/plugin.json';
                if(file_exists($dic)){
                    $appMeta[$key] = json_decode(file_get_contents($dic));
                }
            }

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
            'appMeta' => $appMeta,
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
