<?php

namespace Biz\Util\Service\Impl;

use Biz\BaseService;
use Biz\Util\Service\SystemUtilService;

class SystemUtilServiceImpl extends BaseService implements SystemUtilService
{
    //TODO 删除之前检查该文件是否被其他课程使用
    public function removeUnusedUploadFiles()
    {
        $targets = $this->getSystemUtilDao()->getCourseIdsWhereCourseHasDeleted();
        if (empty($targets)) {
            return 0;
        }
        $targets = $this->plainTargetId($targets);
        $conditions = array(
            'targetType' => 'courselesson',
            'targets' => $targets,
        );
        $uploadFiles = $this->getUploadFileService()->searchFiles(
            $conditions,
            array('createdTime' => 'DESC'),
            0,
            500
        );

        return $this->removeUploadFiles($uploadFiles);
    }

    protected function plainTargetId($targets)
    {
        $result = array();
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
