<?php

namespace MaterialLib\Service\MaterialLib\Impl;

use Topxia\Common\ArrayToolkit;
use MaterialLib\Service\BaseService;
use MaterialLib\Service\MaterialLib\Permission;
use Topxia\Service\Common\AccessDeniedException;
use MaterialLib\Service\MaterialLib\MaterialLibService;

class MaterialLibServiceImpl extends BaseService implements MaterialLibService
{
    public function get($globalId)
    {
        $this->checkPermission(Permission::VIEW, array('globalId' => $globalId));
        return $this->getCloudFileService()->get($globalId);
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

    public function delete($globalId)
    {
        $file = $this->getUploadFileService()->getFileByGlobalId($globalId);

        if ($globalId) {
            $this->checkPermission(Permission::DELETE, array('file' => $file));
            $result = $this->getCloudFileService()->delete($globalId);

            if (isset($result['success']) && $result['success']) {
                $result = $this->getUploadFileService()->deleteByGlobalId($globalId);

                if ($result) {
                    $this->getUploadFileTagService()->deleteByFileId($file['id']);
                }

                return $result;
            }
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

    public function download($globalId)
    {
        $this->checkPermission(Permission::VIEW, array('globalId' => $globalId));
        return $this->getCloudFileService()->download($globalId);
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
