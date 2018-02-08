<?php

namespace Tests\Unit\File;

use Biz\BaseTestCase;

class UploadFileShareHistoryServiceTest extends BaseTestCase
{
    public function testGetShareHistory()
    {
        $history = $this->getUploadFileShareHistoryService()->addShareHistory(1, 2, 1);

        $result = $this->getUploadFileShareHistoryService()->getShareHistory($history['id']);

        $this->assertArrayEquals($history, $result);
    }

    public function testAddShareHistory()
    {
        $result = $this->getUploadFileShareHistoryService()->addShareHistory(1, 2, 1);

        $this->assertNotNull($result);
    }

    public function testFindShareHistory()
    {
        $this->getUploadFileShareHistoryService()->addShareHistory(1, 2, 1);
        $this->getUploadFileShareHistoryService()->addShareHistory(1, 3, 1);

        $results = $this->getUploadFileShareHistoryService()->findShareHistory(1);
        $this->assertEquals(2, count($results));
    }

    public function testSearchShareHistoryCount()
    {
        $this->getUploadFileShareHistoryService()->addShareHistory(1, 2, 1);
        $this->getUploadFileShareHistoryService()->addShareHistory(1, 3, 1);
        $this->getUploadFileShareHistoryService()->addShareHistory(1, 4, 0);

        $count = $this->getUploadFileShareHistoryService()->searchShareHistoryCount(array('isActive' => 1));
        $this->assertEquals(2, $count);
    }

    public function testSearchShareHistories()
    {
        $history1 = $this->getUploadFileShareHistoryService()->addShareHistory(1, 2, 1);
        $history2 = $this->getUploadFileShareHistoryService()->addShareHistory(1, 3, 1);
        $history3 = $this->getUploadFileShareHistoryService()->addShareHistory(1, 4, 0);

        $results = $this->getUploadFileShareHistoryService()->searchShareHistories(array('isActive' => 0), array(), 0, 5);

        $this->assertEquals(1, count($results));
        $this->assertArrayEquals($history3, $results[0]);
    }

    protected function getUploadFileShareHistoryService()
    {
        return $this->createService('File:UploadFileShareHistoryService');
    }
}
