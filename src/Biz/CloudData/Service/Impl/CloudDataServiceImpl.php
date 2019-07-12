<?php

namespace Biz\CloudData\Service\Impl;

use Biz\BaseService;
use Biz\CloudData\Dao\CloudDataDao;
use Biz\CloudData\Service\CloudDataService;
use Biz\System\Service\LogService;
use Biz\CloudPlatform\CloudAPIFactory;

class CloudDataServiceImpl extends BaseService implements CloudDataService
{
    private $cloudApi;

    public function push($name, array $body = array(), $timestamp = 0, $level = 'normal')
    {
        try {
            return $this->createCloudApi()->push($name, $body, $timestamp);
        } catch (\Exception $e) {
            $this->getLogService()->error('cloud_data', 'push', "{$name} 事件发送失败", array('message' => $e->getMessage()));

            if ('important' == $level) {
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

    protected function createCloudApi()
    {
        if (!$this->cloudApi) {
            $this->cloudApi = CloudAPIFactory::create('event');
        }

        return $this->cloudApi;
    }

    /**
     * 仅给单元测试Mock用
     *
     * @param $api
     */
    public function setCloudApi($api)
    {
        $this->cloudApi = $api;
    }
}
