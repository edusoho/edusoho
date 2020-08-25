<?php

namespace Biz\Certificate\Job;

use Biz\Certificate\Service\RecordService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class CheckCertificateExpireJob extends AbstractJob
{
    public function execute()
    {
        return $this->getRecordService()->checkExpireCertificate();
    }

    /**
     * @return RecordService
     */
    protected function getRecordService()
    {
        return $this->biz->service('Certificate:RecordService');
    }
}
