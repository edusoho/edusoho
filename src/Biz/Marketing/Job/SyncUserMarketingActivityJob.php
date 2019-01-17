<?php

namespace Biz\Marketing\Job;

use Biz\Marketing\Service\UserMarketingActivityService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class SyncUserMarketingActivityJob extends AbstractJob
{
    public function execute()
    {
        try {
            $this->getUserMarketingActivityService()->syncAll();
        } catch (\Exception $e) {
        }
    }

    /**
     * @return UserMarketingActivityService
     */
    protected function getUserMarketingActivityService()
    {
        return $this->biz->service('Marketing:UserMarketingActivityService');
    }
}
