<?php

namespace Tests\Unit\AuditCenter\Service;

use Biz\AuditCenter\Dao\ContentAuditDao;
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

    public function testSearchAuditCount()
    {
        $this->createUserContentAudit(['author' => 1, 'status' => 'illegal']);
        $this->createUserContentAudit(['author' => 2, 'status' => 'pass']);

        $testCount1 = $this->getContentAuditService()->searchAuditCount(['author' => 1]);
        $testCount2 = $this->getContentAuditService()->searchAuditCount(['status' => 'pass']);

        $this->assertEquals(1, $testCount1);
        $this->assertEquals(1, $testCount2);
    }

    public function testSearchAudits()
    {
        $this->createUserContentAudit(['author' => 2, 'status' => 'pass']);
        $this->createUserContentAudit(['targetType' => 2, 'status' => 'illegal']);

        $testSearchContent1 = $this->getContentAuditService()->searchAudits(['sensitiveWordsSearch' => 'content'], [], 0, PHP_INT_MAX);
        $testSearchContent2 = $this->getContentAuditService()->searchAudits(['contentSearch' => 'content'], [], 0, PHP_INT_MAX);

        $this->assertNotEmpty($testSearchContent1);
        $this->assertNotEmpty($testSearchContent2);
    }

    public function testConfirmUserAudit()
    {
        $userAudit = $this->createUserContentAudit();
        $this->getContentAuditService()->confirmUserAudit($userAudit['id'], 'illegal', 3);
        $confirmUserAudit = $this->getContentAuditDao()->get($userAudit['id']);

        $this->assertEquals('illegal', $confirmUserAudit['status']);
        $this->assertEquals(3, $confirmUserAudit['auditor']);
    }

    public function testBatchConfirmUserAuditByIds()
    {
        $userAudit1 = $this->createUserContentAudit();
        $userAudit2 = $this->createUserContentAudit();
        $this->getContentAuditService()->batchConfirmUserAuditByIds([$userAudit1['id'], $userAudit2['id']], 'illegal', 3);

        $confirmUserAudit1 = $this->getContentAuditDao()->get($userAudit1['id']);
        $confirmUserAudit2 = $this->getContentAuditDao()->get($userAudit2['id']);

        $this->assertEquals('illegal', $confirmUserAudit1['status']);
        $this->assertEquals('illegal', $confirmUserAudit2['status']);
        $this->assertEquals(3, $confirmUserAudit1['auditor']);
        $this->assertEquals(3, $confirmUserAudit2['auditor']);
    }

    protected function createUserContentAudit($fields = [])
    {
        $contentAudit = array_merge(
            [
                'targetType' => 1,
                'targetId' => 1,
                'author' => 1,
                'content' => 'audit content',
                'sensitiveWords' => ['audit', 'content'],
                'auditor' => 2,
                'status' => 'pass',
                'createdTime' => time(),
                'updatedTime' => time(),
            ], $fields
        );

        return $this->getContentAuditDao()->create($contentAudit);
    }

    /**
     * @return ContentAuditDao
     */
    protected function getContentAuditDao()
    {
        return $this->getBiz()->dao('AuditCenter:ContentAuditDao');
    }

    /**
     * @return ContentAuditService
     */
    protected function getContentAuditService()
    {
        return $this->getBiz()->service('AuditCenter:ContentAuditService');
    }
}
