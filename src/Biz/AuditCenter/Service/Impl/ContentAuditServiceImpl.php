<?php

namespace Biz\AuditCenter\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\AuditCenter\Dao\ContentAuditDao;
use Biz\AuditCenter\Dao\ContentAuditRecordDao;
use Biz\AuditCenter\Service\ContentAuditService;
use Biz\BaseService;
use Biz\Common\CommonException;
use InvalidArgumentException;

class ContentAuditServiceImpl extends BaseService implements ContentAuditService
{
    public function getAudit($id)
    {
        return $this->getContentAuditDao()->get($id);
    }

    public function getAuditByTargetTypeAndTargetId($targetType, $targetId)
    {
        return $this->getContentAuditDao()->getByTargetTypeAndTargetId($targetType, $targetId);
    }

    public function searchAuditCount($conditions)
    {
        $conditions = $this->prepareContentAuditSearchConditions($conditions);

        return $this->getContentAuditDao()->count($conditions);
    }

    public function searchAudits($conditions, $orderBy, $start, $limit)
    {
        $conditions = $this->prepareContentAuditSearchConditions($conditions);

        return $this->getContentAuditDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function confirmUserAudit($id, $status, $auditor)
    {
        $this->checkUserAuditStatus($status);
        $userAudit = $this->getContentAuditDao()->get($id);

        if (empty($userAudit)) {
            throw new \Exception('The audited user content does not exist');
        }

        $confirmFields = [
            'status' => $status,
            'auditor' => $auditor,
        ];

        return $this->getContentAuditDao()->update($id, $confirmFields);
    }

    public function batchConfirmUserAuditByIds($ids, $status, $auditor)
    {
        if (empty($ids)) {
            throw new InvalidArgumentException('Params ids invalid.');
        }

        $this->checkUserAuditStatus($status);

        try {
            $this->beginTransaction();
            foreach ($ids as $id) {
                $this->confirmUserAudit($id, $status, $auditor);
            }
            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    protected function checkUserAuditStatus($status)
    {
        $confirmStatus = ['pass', 'illegal'];

        if (!in_array($status, $confirmStatus)) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }
    }

    protected function prepareContentAuditSearchConditions($conditions)
    {
        $statusAll = ['sysPass', 'none', 'pass', 'illegal'];

        if (!empty($conditions['status']) && !in_array($conditions['status'], $statusAll)) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        return $conditions;
    }

    public function createAudit($fields)
    {
        if (!ArrayToolkit::requireds($fields, ['targetType', 'targetId', 'author', 'content', 'sensitiveWords'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }
        $fields = ArrayToolkit::parts($fields, [
            'targetType',
            'targetId',
            'author',
            'content',
            'sensitiveWords',
            'auditor',
            'status',
            'auditTime',
        ]);

        return $this->getContentAuditDao()->create($fields);
    }

    /**
     * @param $id
     * @param $fields
     * 这个函数是更新审核表数据的基础函数，如果定义类似于审核等函数，最后更新请直接调用此函数
     */
    public function updateAudit($id, $fields)
    {
        $fields = ArrayToolkit::parts($fields, ['content', 'status', 'originStatus', 'auditTimed', 'sensitiveWords']);

        return $this->getContentAuditDao()->update($id, $fields);
    }

    public function getAuditRecord($id)
    {
        return $this->getContentAuditRecordDao()->get($id);
    }

    public function createAuditRecord($fields)
    {
        if (!ArrayToolkit::requireds($fields, ['auditId', 'author', 'content', 'sensitiveWords', 'status', 'originStatus', 'auditTime'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }
        $fields = ArrayToolkit::parts($fields, [
            'auditId',
            'author',
            'content',
            'sensitiveWords',
            'auditor',
            'status',
            'originStatus',
            'auditTime',
        ]);

        return $this->getContentAuditRecordDao()->create($fields);
    }

    public function updateAuditRecord($id, $fields)
    {
        $fields = ArrayToolkit::parts($fields, [
            'content',
            'sensitiveWords',
            'auditor',
            'status',
            'originStatus',
            'auditTime',
        ]);

        return $this->getContentAuditRecordDao()->update($id, $fields);
    }

    /**
     * @param $id
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function deleteAudit($id)
    {
        $this->beginTransaction();
        try {
            $this->getContentAuditDao()->delete($id);
            $this->getContentAuditRecordDao()->deleteByAuditId($id);
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        return true;
    }

    /**
     * @return ContentAuditDao
     */
    protected function getContentAuditDao()
    {
        return $this->biz->dao('AuditCenter:ContentAuditDao');
    }

    /**
     * @return ContentAuditRecordDao
     */
    protected function getContentAuditRecordDao()
    {
        return $this->biz->dao('AuditCenter:ContentAuditRecordDao');
    }
}
