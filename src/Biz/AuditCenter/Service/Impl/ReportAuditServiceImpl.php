<?php

namespace Biz\AuditCenter\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\AuditCenter\Dao\ReportAuditDao;
use Biz\AuditCenter\Service\ReportAuditService;
use Biz\BaseService;
use Biz\Common\CommonException;

class ReportAuditServiceImpl extends BaseService implements ReportAuditService
{
    public function getReportAudit($id)
    {
        return $this->getReportAuditDao()->get($id);
    }

    public function createReportAudit($fields)
    {
        if (!ArrayToolkit::requireds($fields, ['targetType', 'targetId', 'author', 'reportTags', 'content'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }
        $fields = ArrayToolkit::parts($fields, [
            'targetType',
            'targetId',
            'author',
            'reportTags',
            'content',
            'auditor',
            'status',
            'auditTime',
        ]);

        $this->getReportAuditDao()->create($fields);
    }

    /**
     * @param $id
     * @param $fields
     * 这个函数是更新审核表数据的基础函数，如果定义类似于auditReport等函数，最后更新请直接调用此函数
     */
    public function updateReportAudit($id, $fields)
    {
        $fields = ArrayToolkit::parts($fields, [
            'reportTags',
            'content',
            'auditor',
            'status',
            'auditTime',
        ]);

        $this->getReportAuditDao()->update($id, $fields);
    }

    public function deleteReportAudit($id)
    {
        // TODO: Implement deleteReportAudit() method.
    }

    public function getReportAuditRecord($id)
    {
        // TODO: Implement getReportAuditRecord() method.
    }

    public function createReportAuditRecord($fields)
    {
        // TODO: Implement createReportAuditRecord() method.
    }

    public function updateReportAuditRecord($id, $fields)
    {
        // TODO: Implement updateReportAuditRecord() method.
    }

    /**
     * @return ReportAuditDao
     */
    protected function getReportAuditDao()
    {
        return $this->biz->dao('AuditCenter:ReportAuditDao');
    }
}
