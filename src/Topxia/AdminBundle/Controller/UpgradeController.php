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
        $result = $this->getUpgradeService()->install($id);
        var_dump($result);
        exit();
    }

    private function getUpgradeService()
    {
        return $this->getServiceKernel()->createService('Upgrade.UpgradeService');
    }

}