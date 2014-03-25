<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class AppController extends BaseController
{
    public function indexAction(Request $request)
    {

    }

    public function installedAction(Request $request)
    {
        $apps = $this->getAppService()->findApps(0, 100);
        return $this->render('TopxiaAdminBundle:App:installed.html.twig', array(
            'apps' => $apps,
        ));
    }

    public function updatesAction(Request $request)
    {
        $this->getAppService()->checkAppUpgrades();
        return $this->render('TopxiaAdminBundle:App:updates.html.twig', array(

        ));
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

}