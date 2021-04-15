<?php

namespace Biz\AuditCenter\Service\Impl;

use Biz\AuditCenter\Dao\ContentAuditDao;
use Biz\AuditCenter\Service\ContentAuditService;
use Biz\BaseService;
use Biz\Common\CommonException;

class ContentAuditServiceImpl extends BaseService implements ContentAuditService
{
    public function getAudit($id)
    {
        return $this->getContentAuditDao()->get($id);
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

    protected function prepareContentAuditSearchConditions($conditions)
    {
        $statusAll = ['auditing', 'reject' , 'passed'];

        if (!empty($conditions) && !in_array($conditions['status'],$statusAll)) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        return $conditions;
    }

    /**
     * @return ContentAuditDao
     */
    protected function getContentAuditDao()
    {
        return $this->biz->dao('AuditCenter:ContentAuditDao');
    }
}
