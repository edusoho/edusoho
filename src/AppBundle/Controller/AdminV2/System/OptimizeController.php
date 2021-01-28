<?php

namespace AppBundle\Controller\AdminV2\System;

use AppBundle\Controller\AdminV2\BaseController;
use Biz\Util\FileUtil;
use Biz\Util\SystemUtil;

class OptimizeController extends BaseController
{
    public function indexAction()
    {
        return $this->render('admin-v2/system/optimize.html.twig', []);
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
        FileUtil::emptyDir(SystemUtil::getPrivateTmpPath());

        return $this->createJsonResponse(true);
    }

    public function removeBackupAction()
    {
        if (!$this->isDisabledUpgrade()) {
            FileUtil::emptyDir(SystemUtil::getBackUpPath());
        }

        return $this->createJsonResponse(true);
    }

    public function backupDatabaseAction()
    {
        $db = SystemUtil::backupdb();
        $downloadFile = '/files/tmp/'.basename($db);

        return $this->createJsonResponse(['status' => 'ok', 'result' => $downloadFile]);
    }

    protected function isDisabledUpgrade()
    {
        return false;
    }
}
