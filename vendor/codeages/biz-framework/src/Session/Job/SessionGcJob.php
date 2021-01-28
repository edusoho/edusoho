<?php

namespace Codeages\Biz\Framework\Session\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;

class SessionGcJob extends AbstractJob
{
    public function execute()
    {
        $this->getSessionService()->gc();
    }

    protected function getSessionService()
    {
        return $this->biz->service('Session:SessionService');
    }
}
