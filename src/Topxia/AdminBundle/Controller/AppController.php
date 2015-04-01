<?php

namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class AppController extends BaseController
{
    public function indexAction()
    {
    }

    public function oldUpgradeCheckAction()
    {
        return $this->redirect($this->generateUrl('admin_app_upgrades'));
    }

    public function centerAction(Request $request, $postStatus)
    {
        $apps = $this->getAppService()->getCenterApps();
        $theme = array();
        $app = array();
        foreach ($apps as $key => $value) {
            if ($value['type'] == 'theme') {
                $theme[] = $value;
            }elseif ($value['type'] == 'app') {
                $app[] = $value;
            }
        }

        if (isset($apps['error'])) {
            return $this->render('TopxiaAdminBundle:App:center.html.twig', array('status' => 'error','type' => $postStatus));
        }

        if (!$apps) {
            return $this->render('TopxiaAdminBundle:App:center.html.twig', array('status' => 'unlink','type' => $postStatus));
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

    public function installedAction(Request $request, $postStatus)
    {   
        $apps = $this->getAppService()->getCenterApps();

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
}
