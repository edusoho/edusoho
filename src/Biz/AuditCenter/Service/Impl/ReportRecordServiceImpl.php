<?php

namespace Biz\AuditCenter\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\AuditCenter\Dao\ReportRecordDao;
use Biz\AuditCenter\Service\ReportRecordService;
use Biz\BaseService;
use Biz\Common\CommonException;

class ReportRecordServiceImpl extends BaseService implements ReportRecordService
{
    public function getReportRecord($id)
    {
        return $this->getReportRecordDao()->get($id);
    }

    public function getUserReportRecordByTargetTypeAndTargetId($userId, $targetType, $targetId)
    {
        return $this->getReportRecordDao()->getByReporterAndTargetTypeAndTargetId($userId, $targetType, $targetId);
    }

    public function getReportRecordByAuditIdAndReporter($auditId, $reporter)
    {
        return $this->getReportRecordDao()->getByAuditIdAndReporter($auditId, $reporter);
    }

    public function createReportRecord($fields)
    {
        if (!ArrayToolkit::requireds($fields, ['auditId', 'targetType', 'targetId', 'reporter', 'content', 'author', 'reportTags'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }
        $fields = ArrayToolkit::parts($fields, ['auditId', 'targetType', 'targetId', 'reporter', 'content', 'author', 'reportTags', 'auditTime']);

        return $this->getReportRecordDao()->create($fields);
    }

    public function updateReportRecord($id, $fields)
    {
        $fields = ArrayToolkit::parts($fields, ['auditId', 'content', 'author', 'reportTags', 'auditTime']);

        return $this->getReportRecordDao()->update($id, $fields);
    }

    public function findUserReportRecordsByTargetTypeAndTargetIds($userId, $targetType, array $targetIds)
    {
        return $this->getReportRecordDao()->findByReporterAndTargetTypeAndTargetIds($userId, $targetType, $targetIds);
    }

    public function deleteReportRecord($id)
    {
        // TODO: Implement deleteReportRecord() method.
    }

    public function searchReportRecords(array $conditions, array $orderBy, $start, $limit, array $columns = [])
    {
        return $this->getReportRecordDao()->search($conditions, $orderBy, $start, $limit, $columns);
    }

    public function searchReportRecordCount(array $conditions)
    {
        return $this->getReportRecordDao()->count($conditions);
    }

    /**
     * @return ReportRecordDao
     */
    protected function getReportRecordDao()
    {
        return $this->biz->dao('AuditCenter:ReportRecordDao');
    }
}
