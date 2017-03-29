<?php

namespace AppBundle\Controller\Admin;

use Biz\Util\FileUtil;
use Biz\Util\SystemUtil;

class OptimizeController extends BaseController
{
    public function indexAction()
    {
        return $this->render('admin/system/optimize.html.twig', array());
    }

    public function removeCacheAction()
    {
        FileUtil::emptyDir(SystemUtil::getCachePath());

        return $this->createJsonResponse(true);
    }

    public function removeTempAction()
    {
        if (!$this->isDisabledUpgrade()) {
            FileUtil::emptyDir(SystemUtil::getDownloadPath());
        }
        FileUtil::emptyDir(SystemUtil::getUploadTmpPath());

        return $this->createJsonResponse(true);
    }

    public function removeBackupAction()
    {
        if (!$this->isDisabledUpgrade()) {
            FileUtil::emptyDir(SystemUtil::getBackUpPath());
        }

        return $this->createJsonResponse(true);
    }

    public function backupdbAction()
    {
        $db = SystemUtil::backupdb();
        $downloadFile = '/files/tmp/'.basename($db);

        return $this->createJsonResponse(array('status' => 'ok', 'result' => $downloadFile));
    }

    public function removeUnusedFilesAction()
    {
        $result = $this->getSystemUtilService()->removeUnusedUploadFiles();
        if ($result) {
            return $this->createJsonResponse(array('success' => true, 'message' => '优化文件'));
        } else {
            return $this->createJsonResponse(array('success' => false, 'message' => '无可优化文件'));
        }
    }

    public function showProgressbarAction()
    {
        return $this->render('admin/system/progressBar.html.twig');
    }

    protected function isDisabledUpgrade()
    {
        return false;
    }

    protected function getSystemUtilService()
    {
        return $this->createService('Util:SystemUtilService');
    }
}
