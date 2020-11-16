<?php

namespace Tests\Unit\Visualization\Dao;

use Biz\BaseTestCase;
use Biz\Visualization\Dao\UserStayDailyDao;

class UserStayDailyDaoTest extends BaseTestCase
{
    public function testGet()
    {
        $defaultMockFields = $this->getDefaultMockFields();
        $created = $this->getUserStayDailyDao()->create($defaultMockFields);

        $result = $this->getUserStayDailyDao()->get($created['id']);

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

    public function testSumUserPageStayTime()
    {
        $this->getUserStayDailyDao()->batchCreate(
            [
                ['userId' => 1, 'dayTime' => 1604793600, 'sumTime' => 440, 'pureTime' => 220],
                ['userId' => 1, 'dayTime' => 1604880000, 'sumTime' => 540, 'pureTime' => 320],
                ['userId' => 2, 'dayTime' => 1604793600, 'sumTime' => 540, 'pureTime' => 320],
            ]
        );

        $result = $this->getUserStayDailyDao()->sumUserPageStayTime(['userIds' => [1, 2]], 'sumTime');
        $this->assertEquals(980, $result[0]['userStayTime']);
        $this->assertEquals(540, $result[1]['userStayTime']);
    }

    /**
     * @return UserStayDailyDao
     */
    protected function getUserStayDailyDao()
    {
        return $this->biz->dao('Visualization:UserStayDailyDao');
    }
}
