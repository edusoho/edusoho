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
        $package = $this->getAppService()->getCenterPackageInfo(1);

        return $this->render('TopxiaAdminBundle:AppPackageUpdate:modal.html.twig',array(
            'package'=>$package
        ));
    }

    public function checkEnvironmentAction(Request $request, $id)
    {
        $errors = $this->getAppService()->checkPackageUpdateEnvironment();

        if(empty($errors)){
            return $this->createJsonResponse(array('status' => 'ok'));
        }

        // $this->getUpgradeService()->commit($id,$result);
        return $this->createJsonResponse(array('status' => 'error', 'errors'=>$errors));
    }

    public function checkDependsAction(Request $request, $id)
    {
        $errors = $this->getAppService()->checkPackageUpdateDepends($id);

        if(empty($errors)){
            return $this->createJsonResponse(array('status' => 'ok'));
        }
        // $this->getUpgradeService()->commit($id,$errors);
        return $this->createJsonResponse(array('status' => 'error', 'errors'=>$errors));
    }

    public function backupFileAction(Request $request, $id)
    {
        $result = $this->getAppService()->backupFileForPackageUpdate($id);
        if(empty($errors)){
            return $this->createJsonResponse(array('status' => 'ok'));
        }
        // $this->getUpgradeService()->commit($id,$errors);
        return $this->createJsonResponse(array('status' => 'error', 'errors'=>$errors));
    }

    public function backupDbAction(Request $request, $id)
    {
        $result = $this->getAppService()->backupDbForPackageUpdate($id);
        $errors = array();

        if(empty($errors)){
            return $this->createJsonResponse(array('status' => 'ok'));
        }
        // $this->getUpgradeService()->commit($id,$errors);
        return $this->createJsonResponse(array('status' => 'error', 'errors'=>$errors));
    }

    public function downloadAndExtractAction(Request $request, $id)
    {
        $errors = $this->getAppService()->downloadPackageForUpdate($id);
        if(empty($errors)){
            return $this->createJsonResponse(array('status' => 'ok'));
        }
        // $this->getUpgradeService()->commit($id,$errors);
        return $this->createJsonResponse(array('status' => 'error', 'errors'=>$errors));
    }


    public function hasLastErrorAction(Request $request, $id)
    {
        $result = $this->getUpgradeService()->hasLastError($id);
        if(!$result){
            return $this->createJsonResponse(array('status' => 'ok', 'result'=>array()));
        } else {
            return $this->createJsonResponse(array('status' => 'error', 'result'=>array()));
        }
    }

    public function beginUpgradeAction(Request $request, $id)
    {
        $errors = array();

        if(empty($errors)){
            try{
                // $this->getUpgradeService()->refreshCache();
            }catch(\Exception $e){
                $errors = array('升级成功了，但缓存未刷新，请检查 app/cache 权限！');
                return $this->createJsonResponse(array('status' => 'error', 'errors'=>$errors));
            }
            // $this->getUpgradeService()->commit($id,$errors);
            return $this->createJsonResponse(array('status' => 'ok'));
        }

        // $this->getUpgradeService()->commit($id,$errors);
        return $this->createJsonResponse(array('status' => 'error', 'errors'=>$errors));
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }
}