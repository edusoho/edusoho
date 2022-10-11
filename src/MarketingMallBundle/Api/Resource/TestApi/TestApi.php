<?php

namespace MarketingMallBundle\Api\Resource\TestApi;

use Biz\Crontab\SystemCrontabInitializer;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use MarketingMallBundle\Api\Resource\BaseResource;
use ApiBundle\Api\ApiRequest;
use MarketingMallBundle\Biz\SyncList\Service\SyncListService;

class TestApi extends BaseResource
{
    public function add(ApiRequest $request)
    {
        $fields = $request->request->all();
//        $this->getSyncListService()->addSyncList($fields);
        $this->getSchedulerService()->register(array(
            'name' => 'course_task_create_sync_job_'.$fields['type'],
            'source' => SystemCrontabInitializer::SOURCE_SYSTEM,
            'expression' => '* * * * *',
            'misfire_policy' => 'executing',
            'class' => 'MarketingMallBundle\Biz\SyncList\Job\SyncListJob'
        ));
//       return $this->getSyncListService()->getSyncType();
    }

    /**
     * @return SchedulerService
     */
    private function getSchedulerService()
    {
        return $this->getBiz()->service('Scheduler:SchedulerService');
    }

    /**
     * @return SyncListService
     */
    protected function getSyncListService()
    {
        return $this->service('MarketingMallBundle:SyncList:SyncListService');
    }
}
