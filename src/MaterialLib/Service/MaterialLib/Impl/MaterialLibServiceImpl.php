<?php

namespace MaterialLib\Service\MaterialLib\Impl;

use Topxia\Common\ArrayToolkit;
use MaterialLib\Service\BaseService;
use MaterialLib\Service\MaterialLib\MaterialLibService;

class MaterialLibServiceImpl extends BaseService implements MaterialLibService
{
    public function search($conditions, $start, $limit)
    {
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
        return $this->getCloudFileService()->get($globalId);
    }

    public function player($globalId)
    {
        return $this->getCloudFileService()->player($globalId);
    }

    public function edit($globalId, $fields)
    {
        $this->getCloudFileService()->edit($globalId, $fields);
        $this->getUploadFileService()->edit($globalId, $fields);
    }

    public function delete($globalId)
    {
        if ($globalId) {
            $this->getUploadFileService()->deleteByGlobalId($globalId);
            $this->getCloudFileService()->delete($globalId);
        }
        
    }

    public function download($globalId)
    {
        return $this->getCloudFileService()->download($globalId);
    }

    public function reconvert($globalId, $options = array())
    {
        return $this->getCloudFileService()->reconvert($globalId, $options);
    }

    public function getDefaultHumbnails($globalId)
    {
        return $this->getCloudFileService()->getDefaultHumbnails($globalId);
    }

    public function getThumbnail($globalId, $options = array())
    {
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
            $localFiles = $this->getMaterialLibDao()->findFilesByUserId($filterConditions['createdUserId'], $filterConditions['start'], $filterConditions['limit']);
            $globalIds = ArrayToolkit::column($localFiles, 'globalId');
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

    protected function getUploadFileService()
    {
        return $this->createService('File.UploadFileService2');
    }

    protected function getMaterialLibDao()
    {
        return $this->createDao('MaterialLib:MaterialLib.MaterialLibDao');
    }

    protected function getCloudFileService()
    {
        return $this->createService('MaterialLib:MaterialLib.CloudFileService');
    }
}
