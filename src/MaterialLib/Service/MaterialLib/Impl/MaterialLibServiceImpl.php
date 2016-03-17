<?php

namespace MaterialLib\Service\MaterialLib\Impl;

use Topxia\Common\ArrayToolkit;
use MaterialLib\Service\BaseService;
use MaterialLib\Service\MaterialLib\Permission;
use Topxia\Service\Common\AccessDeniedException;
use MaterialLib\Service\MaterialLib\MaterialLibService;

class MaterialLibServiceImpl extends BaseService implements MaterialLibService
{
    public function search($conditions, $start, $limit)
    {
        $this->checkPermission(Permission::SEARCH);
        $conditions['start']    = $start;
        $conditions['limit']    = $limit;
        $conditions             = $this->filterConditions($conditions);
        $result                 = $this->getCloudFileService()->search($conditions);
        $createdUserIds         = ArrayToolkit::column($result['data'], 'createdUserId');
        $result['createdUsers'] = $this->getUserService()->findUsersByIds($createdUserIds);
        return $result;
    }

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
        if ($globalId) {
            $this->checkPermission(Permission::DELETE, array('globalId' => $globalId));
            $result = $this->getCloudFileService()->delete($globalId);
            if (isset($result['success']) && $result['success']) {
                $result = $this->getUploadFileService()->deleteByGlobalId($globalId);
                return $result;
            }
            return false;
        }
    }

    public function batchDelete($ids)
    {
        $files     = $this->getUploadFileService()->findFilesByIds($ids);
        $globalIds = ArrayToolkit::column($files, 'globalId');
        foreach ($globalIds as $key => $value) {
            $this->checkPermission(Permission::DELETE, array('globalId' => $value));
            $result = $this->getCloudFileService()->delete($value);
            if (isset($result['success']) && $result['success']) {
                $result = $this->getUploadFileService()->deleteByGlobalId($value);
                if (!$result) {
                    return false;
                }
            } else {
                return false;
            }
        }
        return array('success' => true);
    }

    public function batchShare($ids)
    {
        $files = $this->getUploadFileService()->findFilesByIds($ids);
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

    protected function filterConditions($conditions)
    {
        $filterConditions = array_filter($conditions, function ($value) {
            if ($value === 0) {
                return true;
            }
            return !empty($value);
        });

        if (!empty($filterConditions['createdUserId'])) {
            $localFiles              = $this->getMaterialLibDao()->findFilesByUserId($filterConditions['createdUserId'], $filterConditions['start'], $filterConditions['limit']);
            $globalIds               = ArrayToolkit::column($localFiles, 'globalId');
            $filterConditions['nos'] = implode(',', $globalIds);
            unset($filterConditions['createdUserId']);
        }

        if (!empty($filterConditions['courseId'])) {
            $localFiles              = $this->getUploadFileService()->findFilesByTypeAndId('courselesson', $filterConditions['courseId']);
            $globalIds               = ArrayToolkit::column($localFiles, 'globalId');
            $filterConditions['nos'] = implode(',', $globalIds);
        }

        return $filterConditions;
    }

    protected function checkPermission($permission, $options = array())
    {
        if (!$this->getPermissionService()->check($permission, $options)) {
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
        return $this->createService('MaterialLib:MaterialLib.CloudFileService');
    }
}
