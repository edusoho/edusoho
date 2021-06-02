<?php

namespace Tests\Unit\SCRM\Service;

use Biz\BaseTestCase;
use Biz\SCRM\Service\SCRMTradeService;

class SCRMTradeServiceTest extends BaseTestCase
{
    /**
     * @return SCRMTradeService
     */
    protected function getSCRMTradeService()
    {
        return $this->getBiz()->service('SCRM:SCRMTradeService');
    }
}
