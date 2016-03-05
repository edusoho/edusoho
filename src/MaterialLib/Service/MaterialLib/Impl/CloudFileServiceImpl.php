<?php

namespace MaterialLib\Service\MaterialLib\Impl;

use MaterialLib\Service\MaterialLib\CloudFileService;
use MaterialLib\Service\BaseService;
use Topxia\Common\ArrayToolkit;

class CloudFileServiceImpl extends BaseService implements CloudFileService
{
    public function search($conditions)
    {
        return $this->getCloudFileImplementor()->search($conditions);
    }

    public function edit($globalId, $fields)
    {
        return $this->getCloudFileImplementor()->edit($globalId, $fields);
    }

    public function delete($globalId)
    {
        $this->getCloudFileImplementor()->delete($globalId);
    }

    public function get($globalId)
    {
        return $this->getCloudFileImplementor()->get($globalId);
    }

    public function download($globalId)
    {
        return $this->getCloudFileImplementor()->download($globalId);
    }

    public function getDefaultHumbnails($globalId)
    {
        return $this->getCloudFileImplementor()->getDefaultHumbnails($globalId);
    }

    protected function getCloudFileImplementor()
    {
        return $this->createService('File.CloudFileImplementor2');
    }

}