<?php

namespace Tests\Unit\AuditCenter\Service;

use Biz\AuditCenter\Service\ContentAuditService;
use Biz\BaseTestCase;

class ContentAuditServiceTest extends BaseTestCase
{
    public function testGetAudit()
    {
//        $res = $this->getContentAuditService()->getAudit();
    }

    /**
     * @return ContentAuditService
     */
    protected function getContentAuditService()
    {
        return $this->getBiz()->service('AuditCenter:ContentAuditService');
    }
}
