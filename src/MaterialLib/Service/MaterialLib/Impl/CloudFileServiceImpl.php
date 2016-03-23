<?php

namespace MaterialLib\Service\MaterialLib\Impl;

use MaterialLib\Service\BaseService;
use MaterialLib\Service\MaterialLib\CloudFileService;

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
        return $this->getCloudFileImplementor()->delete($globalId);
    }

    public function get($globalId)
    {
        return $this->getCloudFileImplementor()->get($globalId);
    }

    public function player($globalId)
    {
        return $this->getCloudFileImplementor()->player($globalId);
    }

    public function download($globalId)
    {
        return $this->getCloudFileImplementor()->download($globalId);
    }

    public function reconvert($globalId, $options = array())
    {
        return $this->getCloudFileImplementor()->reconvert($globalId, $options);
    }

    public function getDefaultHumbnails($globalId)
    {
        return $this->getCloudFileImplementor()->getDefaultHumbnails($globalId);
    }

    public function getThumbnail($globalId, $options)
    {
        return $this->getCloudFileImplementor()->getThumbnail($globalId, $options);
    }

    public function getStatistics($options = array())
    {
        return $this->getCloudFileImplementor()->getStatistics($options);
    }

    public function synData($conditions)
    {
      return $this->getCloudFileImplementor()->synData($conditions);
    }

    protected function getCloudFileImplementor()
    {
        return $this->createService('File.CloudFileImplementor2');
    }

}
