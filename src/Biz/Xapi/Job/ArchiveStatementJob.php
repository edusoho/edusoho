<?php

namespace Biz\Xapi\Job;

use Biz\Xapi\Service\XapiService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class ArchiveStatementJob extends AbstractJob
{
    public function execute()
    {
        $this->getXapiService()->archiveStatement();
    }

    /**
     * @return XapiService
     */
    protected function getXapiService()
    {
        return $this->biz->service('Xapi:XapiService');
    }
}
