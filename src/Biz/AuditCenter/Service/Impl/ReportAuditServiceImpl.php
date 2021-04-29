<?php

namespace Biz\AuditCenter\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\AuditCenter\AuditCenterException;
use Biz\AuditCenter\Dao\ReportAuditDao;
use Biz\AuditCenter\Dao\ReportAuditRecordDao;
use Biz\AuditCenter\ReportSources\AbstractSource;
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
        $this->getReportSource($reportAudit['targetType'])->handleSource($reportAudit);

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

    public function getReportAuditByTargetTypeAndTargetId($targetType, $targetId)
    {
        return $this->getReportAuditDao()->getByTargetTypeAndTargetId($targetType, $targetId);
    }

    public function findReportAuditsByTargetTypeAndTargetId($targetType, $targetId)
    {
        return $this->getReportAuditDao()->findByTargetTypeAndTargetId($targetType, $targetId);
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

        $fields['module'] = $this->getModule($fields['targetType']);

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
            'reportCount',
            'content',
            'auditor',
            'status',
            'auditTime',
        ]);

        return $this->getReportAuditDao()->update($id, $fields);
    }

    public function deleteReportAuditsByIds($ids)
    {
        if (empty($ids)) {
            return;
        }
        foreach ($ids as $id) {
            $this->deleteReportAudit($id);
        }
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

    protected function getModule($targetType)
    {
        $modules = [
            'course_review' => 1,
            'course_review_reply' => 1,
            'classroom_review' => 2,
            'classroom_review_reply' => 2,
            'item_bank_exercise_review' => 3,
            'item_bank_exercise_review_reply' => 3,
            'open_course_review' => 4,
            'open_course_review_reply' => 4,
            'article_review' => 5,
            'article_review_reply' => 5,
            'course_note' => 6,
            'course_thread' => 7,
            'course_thread_reply' => 7,
            'classroom_thread' => 8,
            'classroom_thread_reply' => 8,
            'group_thread' => 9,
            'group_thread_reply' => 9,
            'course_question' => 10,
            'course_question_reply' => 10,
            'classroom_question' => 11,
            'classroom_question_reply' => 11,
            'classroom_event' => 12,
            'classroom_event_reply' => 12,
        ];

        return $modules[$targetType];
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
}
