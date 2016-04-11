<?php

namespace MaterialLib\Service\MaterialLib\Impl;

use Topxia\Common\ArrayToolkit;
use MaterialLib\Service\BaseService;
use MaterialLib\Service\MaterialLib\Permission;
use Topxia\Service\Common\AccessDeniedException;
use MaterialLib\Service\MaterialLib\MaterialLibService;

class MaterialLibServiceImpl extends BaseService implements MaterialLibService
{
    public function get($id)
    {
        //$this->checkPermission(Permission::VIEW, array('id' => $id));
        return $this->getUploadFileService()->getFile($id);
    }

    public function player($globalId)
    {
        $this->checkPermission(Permission::VIEW, array('globalId' => $globalId));
        return $this->getCloudFileService()->player($globalId);
    }

    public function edit($globalId, $fields)
    {
        $this->checkPermission(Permission::EDIT, array('globalId' => $globalId));
        $this->getCloudFileService()->edit($globalId, $fields);
        $this->getUploadFileService()->edit($globalId, $fields);
    }

    public function delete($id)
    {
        $this->checkPermission(Permission::DELETE, array('file' => $file));
        $result = $this->getUploadFileService()->deleteFile($id);

        if ($result) {
            return true;
        }

        return false;
    }

    public function batchDelete($ids)
    {
        $files     = $this->getUploadFileService()->findFilesByIds($ids);
        $globalIds = ArrayToolkit::column($files, 'globalId');

        foreach ($globalIds as $key => $globalId) {
            $result = $this->delete($globalId);
        }

        return array('success' => true);
    }

    public function batchShare($ids)
    {
        $files     = $this->getUploadFileService()->findFilesByIds($ids);
        $globalIds = ArrayToolkit::column($files, 'globalId');

        foreach ($globalIds as $key => $value) {
            $this->checkPermission(Permission::EDIT, array('globalId' => $value));
            $fields = array('isPublic' => '1');

            $result = $this->getUploadFileService()->edit($value, $fields);

            if (!$result) {
                return false;
            } else {
                return true;
            }
        }

        return array('success' => true);
    }

    public function download($id)
    {
        //$this->checkPermission(Permission::VIEW, array('globalId' => $globalId));
        return $this->getUploadFileService()->getDownloadFile($id);
    }

    public function reconvert($globalId, $options = array())
    {
        $this->checkPermission(Permission::EDIT, array('globalId' => $globalId));
        return $this->getCloudFileService()->reconvert($globalId, $options);
    }

    public function getDefaultHumbnails($globalId)
    {
        $this->checkPermission(Permission::VIEW, array('globalId' => $globalId));
        return $this->getCloudFileService()->getDefaultHumbnails($globalId);
    }

    public function getThumbnail($globalId, $options = array())
    {
        $this->checkPermission(Permission::VIEW, array('globalId' => $globalId));
        return $this->getCloudFileService()->getThumbnail($globalId, $options);
    }

    public function getStatistics($options = array())
    {
        return $this->getCloudFileService()->getStatistics($options);
    }

    public function synData()
    {
        $conditions = array(
            'globalId' => '0'
        );
        $oldFiles = $this->getCloudFileService()->synData($conditions);
        return $oldFiles;
    }

    protected function checkPermission($permission, $options = array())
    {
        if (!$this->getPermissionService()->checkPermission($permission, $options)) {
            throw new AccessDeniedException("无权限操作", 403);
        }
    }

    protected function getUploadFileService()
    {
        return $this->createService('File.UploadFileService2');
    }

    //TODO 去除dao
    protected function getMaterialLibDao()
    {
        return $this->createDao('MaterialLib:MaterialLib.MaterialLibDao');
    }

    protected function getPermissionService()
    {
        return $this->createService('MaterialLib:MaterialLib.PermissionService');
    }

    protected function getCloudFileService()
    {
        return $this->createService('CloudFile.CloudFileService');
    }

    protected function getUploadFileTagService()
    {
        return $this->createService('File.UploadFileTagService');
    }
}
