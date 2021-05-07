<?php

namespace Tests\Unit\AuditCenter\Service;

use Biz\AuditCenter\Dao\ReportAuditRecordDao;
use Biz\AuditCenter\Service\ReportAuditService;
use Biz\BaseTestCase;

class ReportAuditServiceTest extends BaseTestCase
{
    public function testSearchReportAudits()
    {
        $audit1 = $this->mockReportAudit(['content' => '举报正文1']);
        $audit2 = $this->mockReportAudit(['content' => '举报正文2', 'targetType' => 'classroom_review', 'author' => 2]);
        $audit3 = $this->mockReportAudit(['content' => '举报正文3', 'status' => 'passed', 'author' => 3]);
        $this->mockBiz('User:UserService', [
            [
                'functionName' => 'getUserByNickname',
                'withParams' => ['author1'],
                'returnValue' => ['id' => 1],
            ],
        ]);

        $result1 = $this->getReportAuditService()->searchReportAudits(['targetType' => 'classroom_review'], ['id' => 'ASC'], 0, 3);
        $result2 = $this->getReportAuditService()->searchReportAudits(['author' => 'author1'], ['id' => 'ASC'], 0, 3);
        $result3 = $this->getReportAuditService()->searchReportAudits(['status' => 'passed'], ['id' => 'ASC'], 0, 3);

        $this->assertEquals([$audit2], $result1);
        $this->assertEquals([$audit1], $result2);
        $this->assertEquals([$audit3], $result3);
    }

    public function testSearchReportAuditCount()
    {
        $this->mockReportAudit(['content' => '举报正文1']);
        $this->mockReportAudit(['content' => '举报正文2', 'targetType' => 'classroom_review', 'author' => 2]);
        $this->mockReportAudit(['content' => '举报正文3', 'status' => 'passed', 'author' => 3]);
        $this->mockBiz('User:UserService', [
            [
                'functionName' => 'getUserByNickname',
                'withParams' => ['author1'],
                'returnValue' => ['id' => 1],
            ],
        ]);

        $result1 = $this->getReportAuditService()->searchReportAuditCount(['targetType' => 'classroom_review'], ['id' => 'ASC'], 0, 3);
        $result2 = $this->getReportAuditService()->searchReportAuditCount(['author' => 'author1'], ['id' => 'ASC'], 0, 3);
        $result3 = $this->getReportAuditService()->searchReportAuditCount(['status' => 'passed'], ['id' => 'ASC'], 0, 3);

        $this->assertEquals(1, $result1);
        $this->assertEquals(1, $result2);
        $this->assertEquals(1, $result3);
    }

    /**
     * @expectedException \Biz\AuditCenter\AuditCenterException
     * @expectedExceptionMessage exception.audit_center.report_audit_status_not_valid
     */
    public function testUpdateReportAuditStatus_whenStatusInvalid_thenThrowException()
    {
        $this->getReportAuditService()->updateReportAuditStatus(1, 'test');
    }

    /**
     * @expectedException \Biz\AuditCenter\AuditCenterException
     * @expectedExceptionMessage exception.audit_center.report_audit_not_exist
     */
    public function testUpdateReportAuditStatus_whenAuditNotExist_thenThrowException()
    {
        $this->getReportAuditService()->updateReportAuditStatus(1, ReportAuditService::STATUS_PASS);
    }

    public function testUpdateReportAuditStatus_whenAuditStatusNotChanged_thenReturn()
    {
        $audit = $this->mockReportAudit();
        $result = $this->getReportAuditService()->updateReportAuditStatus($audit['id'], $audit['status']);
        $this->assertEquals($audit, $result);
    }

    public function testUpdateReportAuditStatus()
    {
        $audit = $this->mockReportAudit();
        $result = $this->getReportAuditService()->updateReportAuditStatus($audit['id'], ReportAuditService::STATUS_PASS);
        $record = $this->getReportAuditRecordDao()->search(['auditId' => $audit['id']], [], 0, 1);

        $this->assertEquals(ReportAuditService::STATUS_NONE, $audit['status']);
        $this->assertEquals(ReportAuditService::STATUS_PASS, $result['status']);
        $this->assertEquals(ReportAuditService::STATUS_NONE, $record[0]['originStatus']);
        $this->assertEquals(ReportAuditService::STATUS_PASS, $record[0]['status']);
    }

    public function testUpdateReportAuditStatusByIds()
    {
        $audit1 = $this->mockReportAudit(['content' => '举报正文1']);
        $audit2 = $this->mockReportAudit(['content' => '举报正文2', 'status' => ReportAuditService::STATUS_PASS, 'author' => 2]);

        $this->getReportAuditService()->updateReportAuditStatusByIds([$audit1['id'], $audit2['id']], ReportAuditService::STATUS_PASS);
        $audit1Result = $this->getReportAuditDao()->get($audit1['id']);
        $audit2Result = $this->getReportAuditDao()->get($audit2['id']);

        $audit1Record = $this->getReportAuditRecordDao()->search(['auditId' => $audit1['id']], [], 0, 1);
        $audit2Record = $this->getReportAuditRecordDao()->search(['auditId' => $audit2['id']], [], 0, 1);

        $this->assertEquals(ReportAuditService::STATUS_NONE, $audit1['status']);
        $this->assertEquals(ReportAuditService::STATUS_PASS, $audit1Result['status']);
        $this->assertEquals(ReportAuditService::STATUS_NONE, $audit1Record[0]['originStatus']);
        $this->assertEquals(ReportAuditService::STATUS_PASS, $audit1Record[0]['status']);
        $this->assertEquals($audit2['status'], $audit2Result['status']);
        $this->assertEmpty($audit2Record);
    }

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
            'status' => ReportAuditService::STATUS_PASS,
            'originStatus' => ReportAuditService::STATUS_ILLEGAL,
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
            'status' => 'none_checked',
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
            'status' => ReportAuditService::STATUS_PASS,
            'originStatus' => ReportAuditService::STATUS_ILLEGAL,
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

    /**
     * @return ReportAuditDao
     */
    protected function getReportAuditDao()
    {
        return $this->biz->dao('AuditCenter:ReportAuditDao');
    }

    /**
     * @return ReportAuditRecordDao
     */
    protected function getReportAuditRecordDao()
    {
        return $this->biz->dao('AuditCenter:ReportAuditRecordDao');
    }
}
