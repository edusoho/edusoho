<?php

namespace Biz\Util\Service\Impl;

use Biz\BaseService;
use Biz\Util\Service\SystemUtilService;

class SystemUtilServiceImpl extends BaseService implements SystemUtilService
{
    protected function plainTargetId($targets)
    {
        $result = [];
        foreach ($targets as $target) {
            $result[] = $target['targetId'];
        }

        return $result;
    }

    protected function removeUploadFiles($uploadFiles)
    {
        $count = 0;
        foreach ($uploadFiles as $file) {
            $result = $this->getUploadFileService()->deleteFile($file['id']);
            $count += $result;
        }

        return $count;
    }

    protected function getSystemUtilDao()
    {
        return $this->createDao('Util:SystemUtilDao');
    }

    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }
}
