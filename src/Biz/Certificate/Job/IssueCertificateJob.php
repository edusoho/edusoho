<?php

namespace Biz\Certificate\Job;

use Biz\Certificate\Service\CertificateService;
use Biz\Certificate\Service\RecordService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class IssueCertificateJob extends AbstractJob
{
    public function execute()
    {
        $certificate = $this->getCertificateService()->get($this->args['certificateId']);
        $strategy = $this->biz['certificate.strategy_context']->createStrategy($certificate['targetType']);

        return $strategy->issueCertificate($certificate);
    }

    /**
     * @return RecordService
     */
    protected function getRecordService()
    {
        return $this->biz->service('Certificate:RecordService');
    }

    /**
     * @return CertificateService
     */
    protected function getCertificateService()
    {
        return $this->biz->service('Certificate:CertificateService');
    }
}
