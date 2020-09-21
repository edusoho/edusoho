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
            'submitter' => 1,
            'eventId' => 1,
        ]);

        $result = $this->getInformationCollectResultService()->isSubmited(1, 1);
        $this->assertEquals(true, $result);
    }

    protected function getInformationCollectResultService()
    {
        return $this->createService('InformationCollect:ResultService');
    }

    protected function getInformationCollectResultDao()
    {
        return $this->createDao('InformationCollect:ResultDao');
    }
}
