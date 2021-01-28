<?php

namespace Biz\Visualization\Job;

use Biz\Crontab\SystemCrontabInitializer;
use Biz\System\Service\CacheService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;

class CheckMediaTypeJob extends AbstractJob
{
    public function execute()
    {
        $data = $this->getCacheService()->gets(['stay_refresh_page', 'learn_refresh_page']);
        if (!empty($data['stay_refresh_page']) || !empty($data['learn_refresh_page'])) {
            $job = [
                'name' => 'UpdateMediaTypeJob',
                'source' => SystemCrontabInitializer::SOURCE_SYSTEM,
                'pool' => 'dedicated',
                'expression' => intval(time()),
                'misfire_policy' => 'executing',
                'class' => 'Biz\Visualization\Job\UpdateMediaTypeJob',
                'args' => [],
            ];

            $this->getSchedulerService()->register($job);
        }
    }

    /**
     * @return CacheService
     */
    protected function getCacheService()
    {
        return $this->biz->service('System:CacheService');
    }

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->biz->service('Scheduler:SchedulerService');
    }
}
