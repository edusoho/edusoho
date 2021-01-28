<?php

namespace Tests;

class OnlineServiceTest extends IntegrationTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->biz['user'] = array(
            'id' => 1,
        );
    }

    public function testCountLogin()
    {
        $mockedSession = $this->mockOnline();
        $online = $this->getOnlineService()->saveOnline($mockedSession);
        $count = $this->getOnlineService()->countLogined(time() - 400);

        $this->assertEquals(1, $count);
    }

    public function testCountTotal()
    {
        $mockedSession = $this->mockOnline();
        $this->biz['user'] = array(
            'id' => 0,
        );
        $this->getOnlineService()->saveOnline($mockedSession);

        $count = $this->getOnlineService()->countLogined(time() - 400);
        $this->assertEquals(0, $count);

        $count = $this->getOnlineService()->countOnline(time() - 400);
        $this->assertEquals(1, $count);
    }

    protected function mockOnline()
    {
        return array(
            'sess_id' => rand(1000000, 9000000),
            'user_id' => 1,
            'user_agent' => 'xxfafaafasfasf',
            'source' => 'web',
            'ip' => '192.178.191.12',
        );
    }

    protected function getOnlineService()
    {
        return $this->biz->service('Session:OnlineService');
    }
}
