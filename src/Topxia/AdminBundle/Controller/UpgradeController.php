<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;

class UpgradeController extends BaseController
{
    public function indexAction(Request $request)
    {
        if ($this->isDisabledUpgrade()) {
            return $this->createDisabledResponse();
        }

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
            'paginator' => $paginator,
            ));
    }

    public function logsAction(Request $request)
    {
        if ($this->isDisabledUpgrade()) {
            return $this->createDisabledResponse();
        }

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
        if ($this->isDisabledUpgrade()) {
            return $this->createDisabledResponse();
        }

        $packagesToUpgrade = $this->getUpgradeService()->check();
        return $this->render('TopxiaAdminBundle:Upgrade:check-result-list.html.twig',array(
            'packages'=>$packagesToUpgrade
        ));
    }

    public function checkCountAction()
    {
        if ($this->isDisabledUpgrade()) {
            return $this->createJsonResponse(false);
        }
        $packagesToUpgrade = $this->getUpgradeService()->check();

        return $this->createJsonResponse(count($packagesToUpgrade));
    }


    public function triggerUpdateModalAction(Request $request, $id)
    {
        if ($this->isDisabledUpgrade()) {
            return $this->createDisabledResponse();
        }

        $updatePackage = $this->getUpgradeService()->getRemoteUpgradePackageInfo($id);
        return $this->render('TopxiaAdminBundle:Upgrade:update-modal.html.twig',array(
            'updatePackage'=>$updatePackage));
    }

    public function checkEnvironmentAction(Request $request, $id)
    {
        if ($this->isDisabledUpgrade()) {
            return $this->createDisabledResponse();
        }

        $result = $this->getUpgradeService()->checkEnvironment();


        if(empty($result)){
            return $this->createJsonResponse(array('status' => 'ok', 'result'=>$result));
        } else {
            $this->getUpgradeService()->commit($id,$result);
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

    private function getUpgradeService()
    {
        return $this->getServiceKernel()->createService('Upgrade.UpgradeService');
    }

    private function isDisabledUpgrade()
    {
        if (!$this->container->hasParameter('disabled_features')) {
            return false;
        }

        $disableds = $this->container->getParameter('disabled_features');
        if (!is_array($disableds) or empty($disableds)) {
            return false;
        }

        return in_array('upgrade', $disableds);
    }

    private function createDisabledResponse()
    {
        return $this->createMessageResponse('error', '自动升级功能已经被关闭！');
    }

}