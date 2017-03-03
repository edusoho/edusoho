<?php

namespace Biz\CloudData\Service\Impl;

use Biz\BaseService;
use Biz\CloudData\Dao\CloudDataDao;
use Biz\CloudData\Service\CloudDataService;
use Biz\System\Service\LogService;
use Biz\CloudPlatform\CloudAPIFactory;

class CloudDataServiceImpl extends BaseService implements CloudDataService
{
    public function push($name, array $body = array(), $timestamp = 0, $level = 'normal')
    {
        try {
            return CloudAPIFactory::create('event')->push($name, $body, $timestamp);
        } catch (\Exception $e) {
            $this->getLogService()->error('cloud_data', 'push', "{$name} 事件发送失败", array('message' => $e->getMessage()));

            if ($level == 'important') {
                $user = $this->getCurrentUser();
                $fields = array(
                    'name' => $name,
                    'body' => $body,
                    'timestamp' => $timestamp,
                    'createdUserId' => $user['id'],
                );
                $this->getCloudDataDao()->create($fields);
            }

            return false;
        }
    }

    public function searchCloudDataCount($conditions)
    {
        return $this->getCloudDataDao()->count($conditions);
    }

    public function searchCloudDatas($conditions, $orderBy, $start, $limit)
    {
        return $this->getCloudDataDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function deleteCloudData($id)
    {
        return $this->getCloudDataDao()->delete($id);
    }

    /**
     * @return CloudDataDao
     */
    protected function getCloudDataDao()
    {
        return $this->createDao('CloudData:CloudDataDao');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
