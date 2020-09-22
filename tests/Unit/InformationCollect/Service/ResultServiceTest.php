<?php

namespace Tests\Unit\InformationCollect\Service;

use Biz\BaseTestCase;

class ResultServiceTest extends BaseTestCase
{
    public function testIsSubmited()
    {
        $result = $this->getInformationCollectResultService()->isSubmited(1, 1);
        $this->assertEquals(false, $result);

        $this->getInformationCollectResultDao()->create([
            'formTitle' => '测试表单',
            'userId' => 1,
            'eventId' => 1,
        ]);

        $result = $this->getInformationCollectResultService()->isSubmited(1, 1);
        $this->assertEquals(true, $result);
    }

    public function testGetResultByUserIdAndEventId()
    {
        $this->getInformationCollectResultDao()->create([
            'id' => 1,
            'formTitle' => '测试表单',
            'userId' => 1,
            'eventId' => 1,
        ]);

        $result = $this->getInformationCollectResultService()->getResultByUserIdAndEventId(1, 1);
        $this->assertEquals(1, $result['id']);
    }

    public function testFindResultItemsByResultId()
    {
        $this->getInformationCollectResultItemDao()->batchCreate([
            ['id' => 1, 'eventId' => 1, 'resultId' => 1, 'code' => 'name', 'labelName' => '姓名', 'value' => '车凌锋'],
            ['id' => 2, 'eventId' => 1, 'resultId' => 1, 'code' => 'gender', 'labelName' => '性别', 'value' => '男'],
            ['id' => 3, 'eventId' => 1, 'resultId' => 2, 'code' => 'gender', 'labelName' => '性别', 'value' => '男'],
        ]);

        $resultItems = $this->getInformationCollectResultService()->findResultItemsByResultId(1);

        $this->assertEquals(2, count($resultItems));
    }

    protected function getInformationCollectResultService()
    {
        return $this->createService('InformationCollect:ResultService');
    }

    protected function getInformationCollectResultDao()
    {
        return $this->createDao('InformationCollect:ResultDao');
    }

    protected function getInformationCollectResultItemDao()
    {
        return $this->createDao('InformationCollect:ResultItemDao');
    }
}
