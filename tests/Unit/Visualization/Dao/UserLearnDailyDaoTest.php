<?php

namespace Tests\Unit\Visualization\Dao;

use Biz\BaseTestCase;
use Biz\Visualization\Dao\UserLearnDailyDao;

class UserLearnDailyDaoTest extends BaseTestCase
{
    public function testGet()
    {
        $defaultMockFields = $this->getDefaultMockFields();
        $created = $this->getUserLearnDailyDao()->create($defaultMockFields);

        $result = $this->getUserLearnDailyDao()->get($created['id']);

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

    public function testFindUserDailyLearnTimeByDate()
    {
        $this->batchMockUserLearnDailyData();

        $result = $this->getUserLearnDailyDao()->findUserDailyLearnTimeByDate(['userId' => 1]);
        $this->assertEquals(440, $result[0]['learnedTime']);
        $this->assertEquals(540, $result[1]['learnedTime']);
    }

    protected function batchMockUserLearnDailyData()
    {
        return $this->getUserLearnDailyDao()->batchCreate(
            [
                ['userId' => 1, 'dayTime' => 1604793600, 'sumTime' => 440, 'pureTime' => 220],
                ['userId' => 1, 'dayTime' => 1604880000, 'sumTime' => 540, 'pureTime' => 320],
                ['userId' => 2, 'dayTime' => 1604793600, 'sumTime' => 540, 'pureTime' => 320],
            ]
        );
    }

    /**
     * @return UserLearnDailyDao
     */
    protected function getUserLearnDailyDao()
    {
        return $this->biz->dao('Visualization:UserLearnDailyDao');
    }
}
