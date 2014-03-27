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
        $result = $this->getAppService()->checkUpdateEnvironment();


        if(empty($result)){
            return $this->createJsonResponse(array('status' => 'ok', 'result'=>$result));
        } else {
            // $this->getUpgradeService()->commit($id,$result);
            return $this->createJsonResponse(array('status' => 'error', 'result'=>$result));
        }
    }

    public function checkDependsAction(Request $request, $id)
    {
        if ($this->isDisabledUpgrade()) {
            return $this->createDisabledResponse();
        }

        $result = $this->getUpgradeService()->checkDepends($id);

        if(empty($result)){
            return $this->createJsonResponse(array('status' => 'ok', 'result'=>$result));
        } else {
            $this->getUpgradeService()->commit($id,$result);
            return $this->createJsonResponse(array('status' => 'error', 'result'=>$result));
        }
    }

    public function downloadAndExtractAction(Request $request, $id)
    {
        if ($this->isDisabledUpgrade()) {
            return $this->createDisabledResponse();
        }

        $result = $this->getUpgradeService()->downloadAndExtract($id);

        if(empty($result)){
            return $this->createJsonResponse(array('status' => 'ok', 'result'=>$result));
        } else {
            $this->getUpgradeService()->commit($id,$result);
            return $this->createJsonResponse(array('status' => 'error', 'result'=>$result));
        }
    }

    public function hasLastErrorAction(Request $request, $id)
    {
        if ($this->isDisabledUpgrade()) {
            return $this->createDisabledResponse();
        }

        $result = $this->getUpgradeService()->hasLastError($id);
        if(!$result){
            return $this->createJsonResponse(array('status' => 'ok', 'result'=>array()));
        } else {
            return $this->createJsonResponse(array('status' => 'error', 'result'=>array()));
        }
    }

    public function backupSystemAction(Request $request, $id)
    {
        if ($this->isDisabledUpgrade()) {
            return $this->createDisabledResponse();
        }
        
        $result = $this->getUpgradeService()->backUpSystem($id);

        if(empty($result)){
            return $this->createJsonResponse(array('status' => 'ok', 'result'=>$result));
        } else {
            $this->getUpgradeService()->commit($id,$result);
            return $this->createJsonResponse(array('status' => 'error', 'result'=>$result));
        }
    }


    public function beginUpgradeAction(Request $request, $id)
    {
        if ($this->isDisabledUpgrade()) {
            return $this->createDisabledResponse();
        }

        $result = $this->getUpgradeService()->beginUpgrade($id);

        if(empty($result)){
            try{
                $this->getUpgradeService()->refreshCache();
            }catch(\Exception $e){
                return $this->createJsonResponse(
                    array('status' => 'error', 
                        'result'=>array('升级成功了，但缓存未刷新，请检查 app/cache 权限！')));
            }
            $this->getUpgradeService()->commit($id,$result);
            return $this->createJsonResponse(array('status' => 'ok', 'result'=>$result));
        } else {
            $this->getUpgradeService()->commit($id,$result);
            return $this->createJsonResponse(array('status' => 'error', 'result'=>$result));
        }

    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }
}