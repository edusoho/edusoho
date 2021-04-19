<?php

namespace Biz\AuditCenter\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\AuditCenter\AuditCenterException;
use Biz\AuditCenter\Dao\ReportAuditDao;
use Biz\AuditCenter\Service\ReportAuditService;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Event\Event;
use Exception;
use InvalidArgumentException;

class ReportAuditServiceImpl extends BaseService implements ReportAuditService
{
    public function searchReportAudits(array $conditions, array $orderBy, $start, $limit, array $columns = [])
    {
        $conditions = $this->prepareSearchReportAuditConditions($conditions);

        return $this->getReportAuditDao()->search($conditions, $orderBy, $start, $limit, $columns);
    }

    public function searchReportAuditCount(array $conditions)
    {
        return $this->getReportAuditDao()->count($this->prepareSearchReportAuditConditions($conditions));
    }

    public function updateReportAuditStatus($id, $status)
    {
        $this->checkReportAuditStatus($status);
        $originReportAudit = $this->getReportAuditDao()->get($id);
        if (empty($originReportAudit)) {
            $this->createNewException(AuditCenterException::REPORT_AUDIT_NOT_EXIST());
        }

        if ($originReportAudit['status'] === $status) {
            return $originReportAudit;
        }

        $reportAudit = $this->updateReportAudit($originReportAudit['id'], [
            'status' => $status,
            'auditor' => $this->getCurrentUser()->getId(),
            'auditTime' => time(),
        ]);

        $this->createReportAuditRecord($this->prepareReportAuditRecord($originReportAudit, $reportAudit));

        return $reportAudit;
    }

    public function updateReportAuditStatusByIds(array $ids, $status)
    {
        if (empty($ids)) {
            throw new InvalidArgumentException('Params ids invalid.');
        }
        $this->checkReportAuditStatus($status);
        try {
            $this->beginTransaction();
            foreach ($ids as $id) {
                $this->updateReportAuditStatus($id, $status);
            }
            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

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

        return $this->getReportAuditDao()->create($fields);
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

        return $this->getReportAuditDao()->update($id, $fields);
    }

    public function deleteReportAudit($id)
    {
        $this->beginTransaction();

        try {
            $reportAudit = $this->getReportAudit($id);
            $this->getReportAuditDao()->delete($id);
            $this->getReportAuditRecordDao()->deleteByAuditId($id);
            $this->dispatchEvent('report_audit.delete', new Event($reportAudit));
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
        }
    }

    public function getReportAuditRecord($id)
    {
        return $this->getReportAuditRecordDao()->get($id);
    }

    public function createReportAuditRecord($fields)
    {
        if (!ArrayToolkit::requireds($fields, ['auditId', 'content', 'author', 'reportTags', 'auditor', 'status', 'originStatus', 'auditTime'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }
        $fields = ArrayToolkit::parts($fields, [
            'auditId',
            'content',
            'author',
            'reportTags',
            'auditor',
            'status',
            'originStatus',
            'auditTime',
        ]);

        return $this->getReportAuditRecordDao()->create($fields);
    }

    public function updateReportAuditRecord($id, $fields)
    {
        $fields = ArrayToolkit::parts($fields, [
            'content',
            'author',
            'reportTags',
            'auditor',
            'status',
            'originStatus',
            'auditTime',
        ]);

        return $this->getReportAuditRecordDao()->update($id, $fields);
    }

    protected function checkReportAuditStatus($status)
    {
        if (!in_array($status, [self::STATUS_NONE, self::STATUS_PASS, self::STATUS_ILLEGAL])) {
            $this->createNewException(AuditCenterException::REPORT_AUDIT_STATUS_INVALID());
        }
    }

    protected function prepareSearchReportAuditConditions($conditions)
    {
        if (isset($conditions['status']) && 'all' === $conditions['status']) {
            unset($conditions['status']);
        }

        if (!empty($conditions['targetTags'])) {
            $conditions['targetTags'] = "|{$conditions['targetTags']}|";
        }

        if (!empty($conditions['author'])) {
            $author = $this->getUserService()->getUserByNickname($conditions['author']);
            $conditions['author'] = empty($author) ? -1 : $author['id'];
        }

        return $conditions;
    }

    protected function prepareReportAuditRecord($originReportAudit, $reportAudit)
    {
        return [
            'auditId' => $reportAudit['id'],
            'content' => $reportAudit['content'],
            'author' => $reportAudit['author'],
            'reportTags' => $reportAudit['reportTags'],
            'auditor' => $reportAudit['auditor'],
            'status' => $reportAudit['status'],
            'originStatus' => $originReportAudit['status'],
            'auditTime' => $reportAudit['auditTime'],
        ];
    }

    /**
     * @return ReportAuditDao
     */
    protected function getReportAuditDao()
    {
        return $this->createDao('AuditCenter:ReportAuditDao');
    }

    /**
     * @return ReportAuditRecordDao
     */
    protected function getReportAuditRecordDao()
    {
        return $this->createDao('AuditCenter:ReportAuditRecordDao');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
