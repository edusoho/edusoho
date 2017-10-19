<?php

namespace Codeages\Biz\Framework\Session\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;

class OnlineGcJob extends AbstractJob
{
    public function execute()
    {
        $this->getOnlineService()->gc();
    }

    protected function getOnlineService()
    {
        return $this->biz->service('Session:OnlineService');
    }
}
