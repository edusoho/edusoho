<?php

namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Util\FileUtil;
use Topxia\Service\Util\SystemUtil;

class OptimizeController extends BaseController
{
    public function indexAction()
    {
       return $this->render('TopxiaAdminBundle:System:optimize.html.twig', array());
    }

    public function removeCacheAction()
    {
         FileUtil::emptyDir(SystemUtil::getCachePath());
        return $this->createJsonResponse(true);
    }
    public function removeTempAction()
    {
        if(!$this->isDisabledUpgrade()){
            FileUtil::emptyDir(SystemUtil::getDownloadPath());
        }
         FileUtil::emptyDir(SystemUtil::getUploadTmpPath());
        return $this->createJsonResponse(true);
    }    

    public function removeBackupAction()
    {
        if(!$this->isDisabledUpgrade()){
             FileUtil::emptyDir(SystemUtil::getBackUpPath());
         }
        return $this->createJsonResponse(true);
    }
    public function backupdbAction()
    {
        $db = SystemUtil::backupdb();
        $downloadFile = '/files/tmp/'.basename($db);
        return $this->createJsonResponse(array('status' => 'ok', 'result'=>$downloadFile));
    }

    public function removeUnusedFilesAction()
    {
        $this->getSystemUtilService()->removeUnusedUploadFiles();
        return $this->createJsonResponse(true);       
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

    private function getSystemUtilService()
    {
        return $this->getServiceKernel()->createService('Util.SystemUtilService');
    }


}