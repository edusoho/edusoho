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

    public function createReportRecord($fields)
    {
        if (!ArrayToolkit::requireds($fields, ['auditId', 'reporter', 'content', 'author', 'reportTags'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }
        $fields = ArrayToolkit::parts($fields, ['auditId', 'reporter', 'content', 'author', 'reportTags', 'auditTime']);

        return $this->getReportRecordDao()->create($fields);
    }

    public function updateReportRecord($id, $fields)
    {
        $fields = ArrayToolkit::parts($fields, ['auditId', 'content', 'author', 'reportTags', 'auditTime']);

        return $this->getReportRecordDao()->update($id, $fields);
    }

    public function deleteReportRecord($id)
    {
        // TODO: Implement deleteReportRecord() method.
    }

    public function searchReportRecords($conditions, $orderBys, $start, $limit, $columns = [])
    {
        // TODO: Implement searchReportRecords() method.
    }

    /**
     * @return ReportRecordDao
     */
    protected function getReportRecordDao()
    {
        return $this->biz->dao('AuditCenter:ReportRecordDao');
    }
}
