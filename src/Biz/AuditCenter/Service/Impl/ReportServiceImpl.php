<?php

namespace Biz\AuditCenter\Service\Impl;

use Biz\AuditCenter\Service\ReportAuditService;
use Biz\AuditCenter\Service\ReportRecordService;
use Biz\AuditCenter\Service\ReportService;
use Biz\BaseService;

class ReportServiceImpl extends BaseService implements ReportService
{
    public function submit($targetType, $targetId, $data)
    {
        $source = $this->getReportSource($targetType);
        $audit = $this->getReportAuditService()->getReportAuditByTargetTypeAndTargetId($targetType, $targetId);
        if (empty($audit)) {
            $audit = $this->getReportAuditService()->createReportAudit($data); //data要格式化
            $data['auditId'] = $audit['id'];
        }

        return $this->getReportRecordService()->createReportRecord($data);
    }

    /**
     * @param $targetType
     *
     * @return mixed
     */
    private function getReportSource($targetType)
    {
        global $kernel;
        $reportSources = $kernel->getContainer()->get('extension.manager')->getReportSources();

        return new $reportSources[$targetType]();
    }

    /**
     * @return ReportRecordService
     */
    protected function getReportRecordService()
    {
        return $this->createService('AuditCenter:ReportRecordService');
    }

    /**
     * @return ReportAuditService
     */
    protected function getReportAuditService()
    {
        return $this->createService('AuditService:ReportAuditService');
    }
}
