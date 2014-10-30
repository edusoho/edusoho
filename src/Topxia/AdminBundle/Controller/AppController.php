<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;
use Topxia\Service\Util\PluginUtil;

class AppController extends BaseController
{
    public function indexAction(Request $request)
    {

    }

    public function oldUpgradeCheckAction()
    {
        return $this->redirect($this->generateUrl('admin_app_upgrades'));
    }

    public function centerAction(Request $request)
    {
        $apps = $this->getAppService()->getCenterApps();

        if(isset($apps['error'])) return $this->render('TopxiaAdminBundle:App:center.html.twig', array('status'=>'error',));
        
        if(!$apps) return $this->render('TopxiaAdminBundle:App:center.html.twig', array('status'=>'unlink',));
        $codes = ArrayToolkit::column($apps, 'code');

        $installedApps = $this->getAppService()->findAppsByCodes($codes);

        return $this->render('TopxiaAdminBundle:App:center.html.twig', array(
            'apps' => $apps,
            'installedApps' => $installedApps,
        ));
    }

    public function installedAction(Request $request)
    {
        $apps = $this->getAppService()->findApps(0, 100);
        return $this->render('TopxiaAdminBundle:App:installed.html.twig', array(
            'apps' => $apps,
        ));
    }

    public function uninstallAction(Request $request)
    {
        $code = $request->get('code');
        $this->getAppService()->uninstallApp($code);
        return $this->createJsonResponse(true);
    }

    public function upgradesAction(Request $request)
    {
        $apps = $this->getAppService()->checkAppUpgrades();

        if(isset($apps['error'])) return $this->render('TopxiaAdminBundle:App:upgrades.html.twig', array('status'=>'error',));
        $version=$this->getAppService()->getMainVersion();

        return $this->render('TopxiaAdminBundle:App:upgrades.html.twig', array(
            'apps' => $apps,
            'version'=>$version,
        ));
    }

    public function upgradesCountAction(Request $request)
    {
        $apps = $this->getAppService()->checkAppUpgrades();
        return $this->createJsonResponse(count($apps));
    }

    public function logsAction(Request $request)
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