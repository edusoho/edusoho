<?php

namespace Biz\AuditCenter\Service\Impl;

use Biz\AuditCenter\AuditCenterException;
use Biz\AuditCenter\Dao\ReportAuditDao;
use Biz\AuditCenter\Service\ReportAuditService;
use Biz\BaseService;
use InvalidArgumentException;

class ReportAuditServiceImpl extends BaseService implements ReportAuditService
{
    const STATUS_NONE = 'none';

    const STATUS_PASS = 'pass';

    const STATUS_ILLEGAL = 'illegal';

    public function searchReportAudits(array $conditions, array $orderBy, $start, $limit, array $columns = [])
    {
        $conditions = $this->prepareSearchConditions($conditions);

        return $this->getReportAuditDao()->search($conditions, $orderBy, $start, $limit, $columns);
    }

    public function searchReportAuditCount(array $conditions)
    {
        return $this->getReportAuditDao()->count($this->prepareSearchConditions($conditions));
    }

    public function updateReportAuditStatus($id, $status)
    {
        $this->checkReportAuditStatus($status);
        $reportAudit = $this->getReportAuditDao()->get($id);

        if (empty($reportAudit)) {
            $this->createNewException(AuditCenterException::REPORT_AUDIT_NOT_EXIST());
        }

        return $this->getReportAuditDao()->update($reportAudit['id'], ['status' => $status]);
    }

    public function updateReportAuditStatusByIds(array $ids, $status)
    {
        if (empty($ids)) {
            throw new InvalidArgumentException('Params ids invalid.');
        }

        $this->checkReportAuditStatus($status);

        return $this->getReportAuditDao()->update(['ids' => $ids], ['status' => $status]);
    }

    protected function checkReportAuditStatus($status)
    {
        if (!in_array($status, [self::STATUS_NONE, self::STATUS_PASS, self::STATUS_ILLEGAL])) {
            $this->createNewException(AuditCenterException::REPORT_AUDIT_STATUS_INVALID());
        }
    }

    protected function prepareSearchConditions($conditions)
    {
        if (isset($conditions['status']) && 'all' === $conditions['status']) {
            unset($conditions['status']);
        }

        return $conditions;
    }

    /**
     * @return ReportAuditDao
     */
    protected function getReportAuditDao()
    {
        return $this->createDao('AuditCenter:ReportAuditDao');
    }
}
