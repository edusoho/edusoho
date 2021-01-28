<?php

namespace Tests\Unit\System\Service;

use Biz\BaseTestCase;
use Biz\System\Service\StatisticsService;

class StatisticsServiceTest extends BaseTestCase
{
    public function testCountOnline()
    {
        $retentionTime = time() - 15 * 60;

        $count = $this->getStatisticsService()->countOnline($retentionTime);

        $this->assertEquals(0, $count);
    }

    public function testCountLogin()
    {
        $retentionTime = time() - 15 * 60;

        $count = $this->getStatisticsService()->countLogin($retentionTime);

        $this->assertEquals(0, $count);
    }

    /**
     * @return StatisticsService
     */
    protected function getStatisticsService()
    {
        return $this->createService('System:StatisticsService');
    }
}
