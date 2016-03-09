<?php

namespace Topxia\Service\CloudData\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\CloudData\CloudDataService;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

class CloudDataServiceImpl extends BaseService implements CloudDataService
{
    public function push($name, array $body = array(), $timestamp, $tryTimes = 0)
    {
        try {
            return CloudAPIFactory::create('event')->push($name, $body, $timestamp);
        } catch (\Exception $e) {
            if ($tryTimes == 0) {
                $tryTimes++;
                return $this->push($name, $body, $timestamp, $tryTimes);
            } else {
                $user   = $this->getCurrentUser();
                $fields = array(
                    'name'          => $name,
                    'body'          => $body,
                    'timestamp'     => $timestamp,
                    'createdUserId' => $user['id']
                );
                $this->getCloudDataDao()->add($fields);
                return false;
            }
        }
    }

    public function searchCloudDataCount($conditions)
    {
        return $this->getCloudDataDao()->searchCloudDataCount($conditions);
    }

    public function searchCloudDatas($conditions, $orderBy, $start, $limit)
    {
        return $this->getCloudDataDao()->searchCloudDatas($conditions, $orderBy, $start, $limit);
    }

    public function deleteCloudData($id)
    {
        return $this->getCloudDataDao()->deleteCloudData($id);
    }

    protected function getCloudDataDao()
    {
        return $this->createDao('CloudData.CloudDataDao');
    }
}
