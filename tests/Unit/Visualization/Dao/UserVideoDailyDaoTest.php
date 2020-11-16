<?php

namespace Tests\Unit\Visualization\Dao;

use Biz\BaseTestCase;
use Biz\Visualization\Dao\UserVideoDailyDao;

class UserVideoDailyDaoTest extends BaseTestCase
{
    public function testGet()
    {
        $defaultMockFields = $this->getDefaultMockFields();
        $created = $this->getUserVideoDailyDao()->create($defaultMockFields);

        $result = $this->getUserVideoDailyDao()->get($created['id']);

        self::assertNotNull($result);
        self::assertEquals($result['userId'], $defaultMockFields['userId']);
    }

    protected function getDefaultMockFields()
    {
        return [
            'userId' => 3,
            'dayTime' => time(),
            'sumTime' => 10,
            'pureTime' => 10,
        ];
    }

    public function testSumUserVideoWatchTime()
    {
        $this->getUserVideoDailyDao()->batchCreate(
            [
                ['userId' => 1, 'dayTime' => 1604793600, 'sumTime' => 440, 'pureTime' => 220],
                ['userId' => 1, 'dayTime' => 1604880000, 'sumTime' => 540, 'pureTime' => 320],
                ['userId' => 2, 'dayTime' => 1604793600, 'sumTime' => 540, 'pureTime' => 320],
            ]
        );

        $result = $this->getUserVideoDailyDao()->sumUserVideoWatchTime(['userIds' => [1, 2]], 'sumTime');
        $this->assertEquals(980, $result[0]['userVideoTime']);
        $this->assertEquals(540, $result[1]['userVideoTime']);
    }

    /**
     * @return UserVideoDailyDao
     */
    protected function getUserVideoDailyDao()
    {
        return $this->biz->dao('Visualization:UserVideoDailyDao');
    }
}
