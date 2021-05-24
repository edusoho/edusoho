<?php

namespace Tests\Unit\SCRM\Service;

use Biz\BaseTestCase;
use Biz\SCRM\Service\SCRMTradeService;

class SCRMTradeServiceTest extends BaseTestCase
{
    public function testGetUserByToken()
    {
        return $this->getSCRMTradeService()->getStatus();
    }

    /**
     * @return SCRMTradeService
     */
    protected function getSCRMTradeService()
    {
        return $this->getBiz()->service('SCRM:SCRMTradeService');
    }
}
