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

        $EduSohoOpenClient = new EduSohoOpenClient();
        if (empty($info['level']) or (!(isset($content['service']['storage'])) and !(isset($content['service']['live'])) and !(isset($content['service']['sms'])) )  ) {
            $articles = $EduSohoOpenClient->getArticles();
            $articles = json_decode($articles, true);
            return $this->render('TopxiaAdminBundle:App:cloud.html.twig', array(
                'articles' => $articles,
            ));
        }

        return $this->redirect($this->generateUrl("admin_my_cloud_overview"));
    }

    public function myCloudOverviewAction(Request $request)
    {
        $content = $this->getEduCloudService()->getUserOverview();
        $info = $this->getEduCloudService()->getAccountInfo();
        if(isset($info['licenseDomains'])) {

            $info['licenseDomainCount'] = count(explode(';', $info['licenseDomains']));

        }

        $isBinded = $this->getAppService()->getBinded();

        $email = isset($isBinded['email']) ? str_replace(substr(substr($isBinded['email'],0,stripos($isBinded['email'], '@')),-4),'****',$isBinded['email']) : null ;

        $EduSohoOpenClient = new EduSohoOpenClient;

        $currentTime = date('Y-m-d', time());

        $account = isset($content['account']) ? $content['account'] : null;
        $day = ''; 
        if (isset($content['account']['arrearageDate']) and  $content['account']['arrearageDate'] != 0 ) {
            $day =ceil( (strtotime($currentTime) - $content['account']['arrearageDate']) /86400) ;
        }

        $user = isset($content['user']) ? $content['user'] : null ;
        $endDate = isset($content['user']['endDate']) ? str_replace('-', '.', $content['user']['endDate']) : '' ;
        $startDate = isset($content['user']['startDate']) ? str_replace('-', '.', $content['user']['startDate']) : '' ;
        $packageDate = isset($content['user']['endDate']) ? ceil((strtotime($content['user']['endDate']) - strtotime($currentTime)) /86400) : '' ;

        $storage = isset($content['service']['storage']) ? $content['service']['storage'] : null ;
        $storageDate = isset($content['service']['storage']['expire']) ? ceil( ($content['service']['storage']['expire'] - strtotime($currentTime) ) /86400) : '' ;
        $month = isset($content['service']['storage']['bill']['date']) ? substr($content['service']['storage']['bill']['date'],0,1) : '' ;
        $startYear = isset($content['service']['storage']['startMonth']) ? substr($content['service']['storage']['startMonth'],0,4) : '' ;
        $startMonth = isset($content['service']['storage']['startMonth']) ? substr($content['service']['storage']['startMonth'],-2) : '' ;
        $endYear = isset($content['service']['storage']['endMonth']) ? substr($content['service']['storage']['endMonth'],0,4) : '' ;
        $endMonth = isset($content['service']['storage']['endMonth']) ? substr($content['service']['storage']['endMonth'],-2) : '' ;
        $storageStart=$startYear.'.'.$startMonth;
        $storageEnd=$endYear.'.'.$endMonth;

        $live = isset($content['service']['live']) ? $content['service']['live'] : null ;
        $liveDate = isset($content['service']['live']['expire']) ?  ceil(($content['service']['live']['expire'] - strtotime($currentTime)) /86400) : '' ;

        $sms = isset($content['service']['sms']) ? $content['service']['sms'] : null ;

        $notices = $EduSohoOpenClient->getNotices();
        $notices = json_decode($notices, true);

        return $this->render('TopxiaAdminBundle:App:my-cloud.html.twig', array(
            'content' =>$content,
            'packageDate' =>$packageDate,
            'storageDate' =>$storageDate,
            'startDate' =>$startDate,
            'endDate' =>$endDate,
            'liveDate' =>$liveDate,
            'storageStart' =>$storageStart,
            'storageEnd' =>$storageEnd,
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
            'email' => $email,
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

            $value['code'] = strtolower($value['code']);
            $apps[$key]['code'] = $value['code'];
            if ($value['type'] == 'theme') {
                $theme[] = $value;
            }elseif ($value['type'] == 'app') {
                $app[] = $value;
            }
            
        }
 
        $installedApps = $this->getAppService()->findApps(0, 100);
        $installedApps = ArrayToolkit::index($installedApps, 'code');

        foreach ($installedApps as $key => $value) {
            
            unset($installedApps[$key]);

            $key = strtolower($key);

            $installedApps[$key] = $value;     

        }

        $showType=$request->query->get("showType");

        return $this->render('TopxiaAdminBundle:App:center.html.twig', array(
            'apps' => $apps,
            'theme' => $theme,
            'allApp' => $app,
            'installedApps' => $installedApps,
            'type' => $postStatus,
            'appTypeChoices' => ($showType == 'hidden') ? 'installedApps' : null
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

        foreach ($apps as $key => $value) {
            
            unset($apps[$key]);

            $appInfo = $value;
            $code = strtolower($key);

            $apps[$code] = $appInfo;
        }
       
        foreach ($appsInstalled as $key => $value) {
            
            $appItem = $key;
            unset($appsInstalled[$key]);

            $appInfo = $value;
            $key = strtolower($key);

            $appsInstalled[$key] = $appInfo;
            $appsInstalled[$key]['installed'] = 1;
            $appsInstalled[$key]['icon'] = !empty($apps[$key]['icon']) ? $apps[$key]['icon'] : null;
            
            if ($key != 'MAIN') {
                if(in_array($key, array("vip", "coupon"))){
                    $key = ucfirst($appItem);
                } else {
                    $key = $appItem;
                }
                $dic = $dir.'/plugins/'.$key.'/plugin.json';
                if(file_exists($dic)){
                    $appMeta[$appItem] = json_decode(file_get_contents($dic));
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
