<?php

namespace Tests\Unit\AuditCenter\Service;

use Biz\AuditCenter\Service\ContentAuditService;
use Biz\BaseTestCase;

class ContentAuditServiceTest extends BaseTestCase
{
    public function testGetAudit()
    {
        $audit1 = $this->mockAudit();
        $res = $this->getContentAuditService()->getAudit($audit1['id']);
        self::assertEquals($audit1, $res);
    }

    public function testCreateAudit()
    {
        $res = $this->getContentAuditService()->createAudit([
            'targetType' => 'course_review',
            'targetId' => 1,
            'author' => 3,
            'content' => '测试创建',
            'sensitiveWords' => [
                '测试',
            ],
        ]);

        self::assertEquals('测试创建', $res['content']);
        self::assertEquals(3, $res['author']);
    }

    public function testUpdateAudit()
    {
        $audit1 = $this->mockAudit(['content' => '测试更新']);
        self::assertEquals('测试更新', $audit1['content']);

        $updated = $this->getContentAuditService()->updateAudit($audit1['id'], ['content' => '更新后的文案']);
        self::assertEquals('更新后的文案', $updated['content']);
    }

    protected function mockAudit($customFields = [])
    {
        return $this->getContentAuditService()->createAudit(array_merge([
            'targetType' => 'course_review',
            'targetId' => 1,
            'author' => 1,
            'content' => '测试正文',
            'sensitiveWords' => [
                '测试',
            ],
        ], $customFields));
    }

    /**
     * @return ContentAuditService
     */
    protected function getContentAuditService()
    {
        return $this->getBiz()->service('AuditCenter:ContentAuditService');
    }
}
