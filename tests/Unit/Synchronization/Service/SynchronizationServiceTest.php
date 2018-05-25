<?php

namespace Tests\Unit\Subtitle\Service;

use Biz\BaseTestCase;
use Biz\Sync\Service\SyncService;
use Biz\Sync\Service\AbstractSychronizer;

class SynchronizationServiceTest extends BaseTestCase
{
    public function testSync()
    {
        $this->getSyncService()->sync('Course:CourseChapter.'.AbstractSychronizer::SYNC_WHEN_CREATE, 1);
    }

    /**
     * @return SyncService
     */
    protected function getSyncService()
    {
        return $this->createService('Sync:SyncService');
    }
}
