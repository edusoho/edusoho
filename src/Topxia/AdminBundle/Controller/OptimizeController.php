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
         FileUtil::emptyDir(SystemUtil::getDownloadPath());
         FileUtil::emptyDir(SystemUtil::getUploadTmpPath());
        return $this->createJsonResponse(true);
    }    

    public function removeBackupAction()
    {
         FileUtil::emptyDir(SystemUtil::getBackUpPath());
        return $this->createJsonResponse(true);
    }
    public function backupdbAction()
    {
        $db = SystemUtil::backupdb();
        $downloadFile = '/files/tmp/'.basename($db);
        return $this->createJsonResponse(array('status' => 'ok', 'result'=>$downloadFile));
    }

}