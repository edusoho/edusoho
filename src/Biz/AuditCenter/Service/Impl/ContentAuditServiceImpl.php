<?php

namespace Biz\AuditCenter\Service\Impl;

use Biz\AuditCenter\Dao\ContentAuditDao;
use Biz\AuditCenter\Service\ContentAuditService;
use Biz\BaseService;

class ContentAuditServiceImpl extends BaseService implements ContentAuditService
{
    public function getAudit($id)
    {
        return $this->getContentAuditDao()->get($id);
    }

    public function createAudit($fields)
    {
    }

    public function updateAudit()
    {
    }

    /**
     * @return ContentAuditDao
     */
    protected function getContentAuditDao()
    {
        return $this->biz->dao('AuditCenter:ContentAuditDao');
    }
}
