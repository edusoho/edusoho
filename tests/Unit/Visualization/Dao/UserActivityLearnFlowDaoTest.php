<?php

namespace Tests\Unit\Visualization\Dao;

use Biz\BaseTestCase;
use Biz\Visualization\Dao\UserActivityLearnFlowDao;

class UserActivityLearnFlowDaoTest extends BaseTestCase
{
    public function testGet()
    {
        $defaultMockFields = $this->getDefaultMockFields();
        $created = $this->getUserActivityLearnFlowDao()->create($defaultMockFields);

        $result = $this->getUserActivityLearnFlowDao()->get($created['id']);

        self::assertNotNull($result);
        self::assertEquals($result['userId'], $defaultMockFields['userId']);
    }

    protected function getDefaultMockFields()
    {
        return [
            'userId' => 3,
            'activityId' => 1,
            'sign' => 'test12345',
            'startTime' => time(),
            'lastLearnTime' => time(),
        ];
    }

    /**
     * @return UserActivityLearnFlowDao
     */
    protected function getUserActivityLearnFlowDao()
    {
        return $this->biz->dao('Visualization:UserActivityLearnFlowDao');
    }
}
