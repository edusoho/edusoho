<?php

namespace Biz\AuditCenter\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\AuditCenter\ReportSources\AbstractSource;
use Biz\AuditCenter\Service\ReportAuditService;
use Biz\AuditCenter\Service\ReportRecordService;
use Biz\AuditCenter\Service\ReportService;
use Biz\BaseService;

class ReportServiceImpl extends BaseService implements ReportService
{
    public function submit($targetType, $targetId, $data)
    {
        $this->beginTransaction();
        try {
            $data = ArrayToolkit::parts($data, ['reportTags', 'reporter']);
            $source = $this->getReportSource($targetType);
            $context = $source->getReportContext($targetId);
            $audit = $this->getReportAuditService()->getReportAuditByTargetTypeAndTargetId($targetType, $targetId);
            if (empty($audit)) {
                $auditInfo = [
                    'targetType' => $targetType,
                    'targetId' => $targetId,
                    'author' => $context['author'],
                    'reportTags' => $data['reportTags'],
                    'content' => $context['content'],
                ];
                $audit = $this->getReportAuditService()->createReportAudit($auditInfo);
            } else {
                $audit = $this->getReportAuditService()->updateReportAudit(
                    $audit['id'],
                    ['reportTags' => array_unique(array_merge($audit['reportTags'], $data['reportTags']))]
                );
            }
            $data['targetType'] = $targetType;
            $data['targetId'] = $targetId;
            $data['auditId'] = $audit['id'];
            $data['content'] = $context['content'];
            $data['author'] = $context['author'];
            $record = $this->getReportRecordService()->createReportRecord($data);
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        return $record;
    }

    public function getReportSourceContext($targetType, $targetId)
    {
        $source = $this->getReportSource($targetType);

        return $source->getReportContext($targetId);
    }

    /**
     * @param $targetType
     *
     * @return AbstractSource
     */
    private function getReportSource($targetType)
    {
        global $kernel;
        $reportSources = $kernel->getContainer()->get('extension.manager')->getReportSources();

        return new $reportSources[$targetType]($this->biz);
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
        return $this->createService('AuditCenter:ReportAuditService');
    }
}
