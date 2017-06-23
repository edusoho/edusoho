<?php

namespace Biz\User\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Topxia\Service\Common\ServiceKernel;

class DeleteSessionJob extends AbstractJob
{
    public function execute()
    {
        $retentionTime = time() - 7200;
        $limit = 500;
        $this->getSessionService()->deleteInvalidSession($retentionTime, $limit);
    }

    protected function getSessionService()
    {
        return $this->getServiceKernel()->createService('System:SessionService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

    protected function getLogService()
    {
        return $this->getServiceKernel()->createService('Log:LogService');
    }
}
