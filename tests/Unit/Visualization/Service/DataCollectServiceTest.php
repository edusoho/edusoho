<?php

namespace Tests\Unit\Visualization\Service;

use Biz\BaseTestCase;
use Biz\Visualization\Service\DataCollectService;

class DataCollectServiceTest extends BaseTestCase
{
    public function testPush()
    {
        $record = $this->getDataCollectService()->push([
            'userId' => 1,
            'activityId' => 1,
            'taskId' => 1,
            'courseId' => 1,
            'courseSetId' => 1,
            'event' => 'doing',
            'client' => 'ios',
            'startTime' => time() - 60,
            'endTime' => time(),
            'duration' => 60, //这里需要做校验，兼容老数据
            'mediaType' => 'video',
            'flowSign' => 'testsign',
            'data' => [
                'userAgent' => 'kuozhi-Android',
            ],
        ]);

        self::assertEquals($record['flowSign'], 'testsign');
    }

    public function testCreateLearnFlow()
    {
        $flow = $this->getDataCollectService()->createLearnFlow(1, 1, 'testsign');
        $res = $this->getDataCollectService()->getFlowBySign(1, $flow['sign']);
        self::assertEquals($flow, $res);
    }

    /**
     * @return DataCollectService
     */
    protected function getDataCollectService()
    {
        return $this->getBiz()->service('Visualization:DataCollectService');
    }
}
