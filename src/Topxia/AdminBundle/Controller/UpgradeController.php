<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;

class UpgradeController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditons = array();
        $paginator = new Paginator(
            $this->get('request'),
            $this->getUpgradeService()->searchPackageCount($conditons),
            20
        );

        $findedPackages = $this->getUpgradeService()->searchPackages(
            $conditons,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount());

        return $this->render('TopxiaAdminBundle:Upgrade:index.html.twig',array(
            'packages'=>$findedPackages,
            'paginator' => $paginator
            ));
    }

    public function logsAction(Request $request)
    {
        $conditions = array();

        $paginator = new Paginator(
            $this->get('request'),
            $this->getUpgradeService()->searchLogCount($conditions),
            20
        );

        $logs = $this->getUpgradeService()->searchLogs(
            $conditions,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount());

        return $this->render('TopxiaAdminBundle:Upgrade:upgrade-logs-list.html.twig',array(
            'logs'=>$logs,
            'paginator' => $paginator
            ));
    }


    public function checkAction(Request $request)
    {
        $packagesToUpgrade = $this->getUpgradeService()->check();
        return $this->render('TopxiaAdminBundle:Upgrade:check-result-list.html.twig',array(
            'packages'=>$packagesToUpgrade
        ));
    }

    public function triggerInstallModalAction(Request $request, $id)
    {
        $installPackage = $this->getUpgradeService()->getRemoteInstallPackageInfo($id);
        return $this->render('TopxiaAdminBundle:Upgrade:install-modal.html.twig', array(
            'installPackage'=>$installPackage));
    }

    public function triggerUpdateModalAction(Request $request, $id)
    {
        $updatePackage = $this->getUpgradeService()->getRemoteUpgradePackageInfo($id);
        return $this->render('TopxiaAdminBundle:Upgrade:update-modal.html.twig',array(
            'updatePackage'=>$updatePackage));
    }

    public function installAction(Request $request, $id)
    {
        $result = $this->getUpgradeService()->checkEnvironment();
        var_dump($result);

        $result = $this->getUpgradeService()->checkDepends($id);
        var_dump($result);
        $result = $this->getUpgradeService()->downloadAndExtract($id);
        var_dump($result);
        // $result = $this->getUpgradeService()->backUpSystem($id);
        //  var_dump($result);

        return $this->createJsonResponse(array('status' => 'ok', 'packageId'=>$id));
    }

    public function upgradeAction(Request $request, $id)
    {
        $result = $this->getUpgradeService()->checkEnvironment();
            var_dump($result);
    
        $result = $this->getUpgradeService()->checkDepends($id);
        var_dump($result);
        $result = $this->getUpgradeService()->downloadAndExtract($id);
         var_dump($result);
        $result = $this->getUpgradeService()->backUpSystem($id);
         var_dump($result);  

               $result = $this->getUpgradeService()->beginUpgrade($id);
         var_dump($result);

    // public function checkDepends($id);
    // public function downloadAndExtract($id);
    // public function backUpSystem($id);
    // public function beginUpgrade($id);


        // TODO
        // $this->getUpgradeService()->backUpdirectories($directoryAfterUnZip);

        // $package = $this->getUpgradeService()->getRemoteUpgradePackageInfo($id);
        // $this->getUpgradeService()->addInstalledPackage($package);
        return $this->createJsonResponse(array('status' => 'ok', 'packageId'=>$id));
    }

    public function checkEnvironmentAction(Request $request)
    {
        $result = $this->getUpgradeService()->checkEnvironment();
        if(empty($result)){
            return $this->createJsonResponse(array('status' => 'ok', 'result'=>$result));
        } else {
            return $this->createJsonResponse(array('status' => 'error', 'result'=>$result));
        }
    }

    public function checkDependsAction(Request $request, $id)
    {
        $result = $this->getUpgradeService()->checkDepends($id);
        if(empty($result)){
            return $this->createJsonResponse(array('status' => 'ok', 'result'=>$result));
        } else {
            return $this->createJsonResponse(array('status' => 'error', 'result'=>$result));
        }
    }

    public function downloadAndExtractAction(Request $request, $id)
    {
        $result = $this->getUpgradeService()->downloadAndExtract($id);
        if(empty($result)){
            return $this->createJsonResponse(array('status' => 'ok', 'result'=>$result));
        } else {
            return $this->createJsonResponse(array('status' => 'error', 'result'=>$result));
        }
    }

    public function backupSystemAction(Request $request, $id)
    {
        $result = $this->getUpgradeService()->backUpSystem($id);
        if(empty($result)){
            return $this->createJsonResponse(array('status' => 'ok', 'result'=>$result));
        } else {
            return $this->createJsonResponse(array('status' => 'error', 'result'=>$result));
        }
    }

    public function beginUpgradeAction(Request $request, $id)
    {
        $result = $this->getUpgradeService()->beginUpgrade($id);
        if(empty($result)){
            return $this->createJsonResponse(array('status' => 'ok', 'result'=>$result));
        } else {
            return $this->createJsonResponse(array('status' => 'error', 'result'=>$result));
        }
    }

    private function getUpgradeService()
    {
        return $this->getServiceKernel()->createService('Upgrade.UpgradeService');
    }

}