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

    /**
     * @return UserLearnDailyDao
     */
    protected function getUserLearnDailyDao()
    {
        return $this->biz->dao('Visualization:UserLearnDailyDao');
    }
}
