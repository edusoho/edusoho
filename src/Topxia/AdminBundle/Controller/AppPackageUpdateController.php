<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class AppPackageUpdateController extends BaseController
{
    public function modalAction(Request $request, $id)
    {
        $package = $this->getAppService()->getCenterPackageInfo($id);

        return $this->render('TopxiaAdminBundle:AppPackageUpdate:modal.html.twig',array(
            'package'=>$package
        ));
    }

    public function checkEnvironmentAction(Request $request, $id)
    {
        $errors = $this->getAppService()->checkEnvironmentForPackageUpdate($id);
        return $this->createResponseWithErrors($errors);
    }

    public function checkDependsAction(Request $request, $id)
    {
        $errors = $this->getAppService()->checkDependsForPackageUpdate($id);
        return $this->createResponseWithErrors($errors);
    }

    public function backupFileAction(Request $request, $id)
    {
        $errors = $this->getAppService()->backupFileForPackageUpdate($id);
        return $this->createResponseWithErrors($errors);
    }

    public function backupDbAction(Request $request, $id)
    {
        $errors = $this->getAppService()->backupDbForPackageUpdate($id);
        return $this->createResponseWithErrors($errors);
    }

    public function downloadAndExtractAction(Request $request, $id)
    {
        $errors = $this->getAppService()->downloadPackageForUpdate($id);
        return $this->createResponseWithErrors($errors);
    }

    public function checkDownloadAndExtractAction(Request $request, $id)
    {
        $errors = $this->getAppService()->checkDownloadPackageForUpdate($id);
        return $this->createResponseWithErrors($errors);
    }

    public function checklastErrorAction(Request $request, $id)
    {
        $result = $this->getAppService()->hasLastErrorForPackageUpdate($id);
        return $this->createJsonResponse($result);
    }

    public function beginUpgradeAction(Request $request, $id)
    {
        $errors = $this->getAppService()->beginPackageUpdate($id);
        return $this->createResponseWithErrors($errors);
    }

    private function createResponseWithErrors($errors)
    {
        if (empty($errors)) {
            return $this->createJsonResponse(array('status' => 'ok'));
        }
        return $this->createJsonResponse(array('status' => 'error', 'errors'=>$errors));
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }
}