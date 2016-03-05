<?php

namespace MaterialLib\Service\MaterialLib\Impl;

use MaterialLib\Service\MaterialLib\MaterialLibService;
use MaterialLib\Service\BaseService;
use Topxia\Common\ArrayToolkit;

class MaterialLibServiceImpl extends BaseService implements MaterialLibService
{
    public function search($conditions, $start, $limit)
    {
        $conditions['start'] = $start;
        $conditions['limit'] = $limit;
        $conditions = $this->filterConditions($conditions);
        return $this->getCloudFileService()->search($conditions);
    }

    public function get($globalId)
    {
        return $this->getCloudFileService()->get($globalId);
    }

    public function edit($globalId, $fields)
    {
        return $this->getCloudFileService()->edit($globalId, $fields);
    }

    public function delete($globalId)
    {
        $this->getUploadFileService()->deleteByGlobalId($globalId);
        $this->getCloudFileService()->delete($globalId);
    }

    public function download($globalId)
    {
        return $this->getCloudFileService()->download($globalId);
    }

    public function getDefaultHumbnails($globalId)
    {
        return $this->getCloudFileService()->getDefaultHumbnails($globalId);
    }

    protected function filterConditions($conditions)
    {
        $filterConditions = array_filter($conditions);

        if (!empty($filterConditions['createdUserId'])) {
            $filterConditions['endUser'] = $filterConditions['createdUserId'];
            unset($filterConditions['createdUserId']);
        }

        if (!empty($filterConditions['courseId'])) {
            $localFiles = $this->getCloudFileService()->findFilesByTypeAndId('courselesson', $filterConditions['courseId']);
            $globalIds = ArrayToolkit::column($localFiles, 'globalId');
            $filterConditions['nos'] = implode(',', $globalIds);
        }

        return $filterConditions;
    }

    protected function getUploadFileService()
    {
        return $this->createService('File.UploadFileService2');
    }

    protected function getCloudFileService()
    {
        return $this->createService('MaterialLib:MaterialLib.CloudFileService');
    }
}
