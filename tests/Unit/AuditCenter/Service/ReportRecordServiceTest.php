<?php

namespace Tests\Unit\AuditCenter\Service;

use Biz\AuditCenter\Service\ReportRecordService;
use Biz\BaseTestCase;

class ReportRecordServiceTest extends BaseTestCase
{
    public function testGetReportRecord()
    {
        $record1 = $this->mockReportRecord();
        $res = $this->getReportRecordService()->getReportRecord($record1['id']);
        self::assertEquals($record1, $res);
    }

    public function testCreateReportRecord()
    {
        $res = $this->getReportRecordService()->createReportRecord([
            'auditId' => 1,
            'targetType' => 'course_review',
            'targetId' => 1,
            'reporter' => 10,
            'content' => '测试创建',
            'author' => 3,
            'reportTags' => [1, 3],
        ]);

        self::assertEquals(3, $res['author']);
        self::assertEquals('测试创建', $res['content']);
    }

    public function testUpdateReportRecord()
    {
        $record1 = $this->mockReportRecord([
            'content' => '创建记录',
        ]);
        self::assertEquals('创建记录', $record1['content']);

        $updated = $this->getReportRecordService()->updateReportRecord($record1['id'], ['content' => '更新的创建记录']);
        self::assertEquals('更新的创建记录', $updated['content']);
    }

    public function testSearchReportRecords()
    {
        $audit1 = $this->mockReportRecord(['auditId' => '1']);
        $audit2 = $this->mockReportRecord(['auditId' => '2']);

        $result = $this->getReportRecordService()->searchReportRecords(['auditId' => $audit2['id']], ['id' => 'ASC'], 0, 3);

        $this->assertEquals([$audit2], $result);
    }

    public function testSearchReportAuditRecordCount()
    {
        $audit1 = $this->mockReportRecord(['auditId' => '1']);
        $audit2 = $this->mockReportRecord(['auditId' => '2']);

        $result = $this->getReportRecordService()->searchReportRecordCount(['auditId' => $audit2['id']], ['id' => 'ASC'], 0, 3);

        $this->assertEquals(1, $result);
    }

    protected function mockReportRecord($customFields = [])
    {
        return $this->getReportRecordService()->createReportRecord(array_merge([
            'auditId' => 1,
            'targetType' => 'course_review',
            'targetId' => 1,
            'reporter' => 10,
            'content' => '测试正文',
            'author' => 1,
            'reportTags' => [1, 3],
        ], $customFields));
    }

    /**
     * @return ReportRecordService
     */
    protected function getReportRecordService()
    {
        return $this->biz->service('AuditCenter:ReportRecordService');
    }
}
