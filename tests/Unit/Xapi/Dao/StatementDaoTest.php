<?php

namespace Tests\Unit\Xapi\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class StatementDaoTest extends BaseDaoTestCase
{
    public function testCallbackStatusPushedAndPushedTimeByUuids()
    {
        $result = $this->mockDataObject();
        $uuid = $result['uuid'];
        $pushTime = '123456';

        $update = $this->getDao()->callbackStatusPushedAndPushedTimeByUuids(array($uuid), $pushTime);

        $get = $this->getDao()->get($result['id']);
        $this->assertEquals('123456', $get['push_time']);

        $this->assertEquals(true, $update);
    }

    public function testCallbackStatusPushedAndPushedTimeByUuidsWithEmptyIds()
    {
        $pushTime = '123456';
        $update = $this->getDao()->callbackStatusPushedAndPushedTimeByUuids(array(), $pushTime);
        $this->assertEmpty($update);
    }

    public function testRetryStatusPushingToCreatedByCreatedTime()
    {
        $time = time();
        $result = $this->mockDataObject(array(
            'status' => 'pushing',
            'created_time' => $time - 86400,
        ));

        $update = $this->getDao()->retryStatusPushingToCreatedByCreatedTime($time - 86400 * 3);
        $this->assertEquals(true, $update);

        $get = $this->getDao()->get($result['id']);

        $this->assertEquals('created', $get['status']);
    }

    protected function getDefaultMockFields()
    {
        return array(
            'uuid' => '18052A11-029B-5010-4C26-57906C9C0D52',
            'version' => '1.0.0',
            'user_id' => 2,
            'verb' => 'finish',
            'target_id' => 10,
            'target_type' => 'activity',
            'occur_time' => '1234567',
        );
    }
}
