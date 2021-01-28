<?php

namespace Tests\Unit\Question;

use Biz\BaseTestCase;
use Biz\QiQiuYun\Service\QiQiuYunSdkProxyService;
use Mockery;

class QiQiuYunSdkProxyServiceTest extends BaseTestCase
{
    public function testPushEventTracking()
    {
        $biz = $this->getBiz();
        $esOpService = Mockery::mock('QiQiuYun\SDK\Service\ESopService');
        $esOpService->shouldReceive('pushEventTracking')->andReturnNull();
        $biz['qiQiuYunSdk.esOp'] = $esOpService;
        $this->getQiQiuYunSdkProxyService()->pushEventTracking('test', null);
    }

    /**
     * @return QiQiuYunSdkProxyService
     */
    protected function getQiQiuYunSdkProxyService()
    {
        return $this->biz->service('QiQiuYun:QiQiuYunSdkProxyService');
    }
}
