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
        $directoryAfterUnZip = $this->getUpgradeService()->install($id);
        // TODO
        $this->getUpgradeService()->backUpdirectories($directoryAfterUnZip);

        // $package = $this->getUpgradeService()->getRemoteInstallPackageInfo($id);
        // $this->getUpgradeService()->addInstalledPackage($package);
        return $this->createJsonResponse(array('status' => 'ok', 'packageId'=>$id));
    }

    public function upgradeAction(Request $request, $id)
    {
        $directoryAfterUnZip = $this->getUpgradeService()->upgrade($id);
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