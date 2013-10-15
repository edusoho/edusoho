<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;

class UpgradeController extends BaseController
{
    public function indexAction(Request $request)
    {
        $paginator = new Paginator(
            $this->get('request'),
            $this->getUpgradeService()->searchPackageCount(),
            20
        );

        $findedPackages = $this->getUpgradeService()->searchPackages($paginator->getOffsetCount(),
            $paginator->getPerPageCount());

        return $this->render('TopxiaAdminBundle:Upgrade:index.html.twig',array(
            'packages'=>$findedPackages,
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

    private function getUpgradeService()
    {
        return $this->getServiceKernel()->createService('Upgrade.UpgradeService');
    }

}