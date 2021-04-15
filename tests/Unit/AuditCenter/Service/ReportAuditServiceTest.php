<?php

namespace Tests\Unit\AuditCenter\Service;

use Biz\AuditCenter\Service\ReportAuditService;
use Biz\BaseTestCase;

class ReportAuditServiceTest extends BaseTestCase
{
    public function testGetReportAudit()
    {
        $res = $this->mockReportAudit(['content' => '举报正文']);
        $get = $this->getReportAuditService()->getReportAudit($res['id']);
        self::assertEquals('举报正文', $get['content']);
    }

    public function testCreateReportAudit()
    {
        $res = $this->getReportAuditService()->createReportAudit([
            'targetType' => 'course_review',
            'targetId' => 1,
            'author' => 1,
            'reportTags' => [1, 4],
            'content' => '举报正文',
        ]);

        self::assertEquals('举报正文', $res['content']);
        self::assertEquals('course_review', $res['targetType']);
    }

    public function testUpdateReportAudit()
    {
        $audit1 = $this->mockReportAudit(['content' => '举报正文']);
        self::assertEquals('举报正文', $audit1['content']);

        $updated = $this->getReportAuditService()->updateReportAudit($audit1['id'], ['content' => '更新后的原文']);
        self::assertEquals('更新后的原文', $updated['content']);
    }

    public function testDeleteReportAudit()
    {
    }

    public function testGetReportAuditRecord()
    {
        $record1 = $this->mockReportAuditRecord(['content' => '原正文']);
        $res = $this->getReportAuditService()->getReportAuditRecord($record1['id']);

        self::assertEquals($record1, $res);
    }

    public function testCreateReportAuditRecord()
    {
        $res = $this->getReportAuditService()->createReportAuditRecord([
            'auditId' => 1,
            'content' => '举报正文',
            'author' => 1,
            'reportTags' => [1, 5],
            'auditor' => 3,
            'status' => 'passed',
            'originStatus' => 'rejected',
            'auditTime' => time(),
        ]);
        self::assertEquals('举报正文', $res['content']);
    }

    public function testUpdateReportAuditRecord()
    {
        $record1 = $this->mockReportAuditRecord(['content' => '原正文']);
        self::assertEquals('原正文', $record1['content']);

        $updated = $this->getReportAuditService()->updateReportAuditRecord($record1['id'], ['content' => '更新后的正文']);
        self::assertEquals('更新后的正文', $updated['content']);
    }

    protected function mockReportAudit($customFields = [])
    {
        return $this->getReportAuditService()->createReportAudit(array_merge([
            'targetType' => 'course_review',
            'targetId' => 1,
            'author' => 1,
            'reportTags' => [1, 4],
            'content' => '举报正文',
        ], $customFields));
    }

    protected function mockReportAuditRecord($customFields = [])
    {
        return $this->getReportAuditService()->createReportAuditRecord(array_merge([
            'auditId' => 1,
            'content' => '举报正文',
            'author' => 1,
            'reportTags' => [1, 5],
            'auditor' => 3,
            'status' => 'passed',
            'originStatus' => 'rejected',
            'auditTime' => time(),
        ], $customFields));
    }

    /**
     * @return ReportAuditService
     */
    protected function getReportAuditService()
    {
        return $this->biz->service('AuditCenter:ReportAuditService');
    }
}
