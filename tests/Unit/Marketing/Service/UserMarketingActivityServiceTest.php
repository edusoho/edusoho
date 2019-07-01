<?php

namespace Tests\Unit\Marketing\Service;

use Biz\BaseTestCase;
use Biz\Marketing\Service\UserMarketingActivitySynclogService;

class UserMarketingActivityServiceTest extends BaseTestCase
{
    public function testSearchActivities()
    {
        $this->createDefaultActivity(array('userId' => 1));
        $this->createDefaultActivity(array('userId' => 2));
        $conditions = array('userId' => 1);
        $result = $this->getUserMarketingActivityService()->searchActivities($conditions, array(), 0, 1);

        $this->assertEquals($result[0]['userId'], 1);
    }

    public function testSearchActivityCount()
    {
        $this->createDefaultActivity(array('userId' => 1));
        $this->createDefaultActivity(array('userId' => 2));
        $this->createDefaultActivity(array('userId' => 2));
        $result = $this->getUserMarketingActivityService()->searchActivityCount(array('userId' => 2));

        $this->assertEquals($result, 2);
    }

    /**
     * @expectedException \Biz\User\UserException
     * @expectedExceptionMessage exception.user.not_found
     */
    public function testSyncByMobileWithErrorMobile()
    {
        $this->getUserMarketingActivityService()->syncByMobile('12333333333');
    }

    public function testSyncByMobileWithEmptyLog()
    {
        $mockedUserService = $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'getUserByVerifiedMobile',
                    'withParams' => array('17777777777'),
                    'returnValue' => array(
                        'id' => 10,
                    ),
                ),
            )
        );

        $this->getUserMarketingActivityService()->syncByMobile('17777777777');

        $result = $this->getUserMarketingActivitySynclogService()->getLastSyncLogByTargetAndTargetValue(UserMarketingActivitySynclogService::TARGET_MOBILE, '17777777777');

        $this->assertEquals($result['args']['target_value'], '17777777777');
    }

    public function testFindByJoinedIdAndType()
    {
        $this->createDefaultActivity();
        $result = $this->getUserMarketingActivityService()->findByJoinedIdAndType(2, 'testType');

        $this->assertEquals($result['name'], 'testName');
    }

    protected function createDefaultActivity($fields = array())
    {
        $default = array(
            'userId' => 2,
            'mobile' => '17777777777',
            'activityId' => 2,
            'joinedId' => 2,
            'name' => 'testName',
            'type' => 'testType',
            'status' => 'published',
            'cover' => 'test',
            'itemType' => 'course',
            'itemSourceId' => 1,
        );

        $fields = array_merge($default, $fields);

        return $this->getUserMarketingActivityDao()->create($fields);
    }

    protected function getUserMarketingActivityService()
    {
        return $this->createService('Marketing:UserMarketingActivityService');
    }

    protected function getUserMarketingActivityDao()
    {
        return $this->createDao('Marketing:UserMarketingActivityDao');
    }

    protected function getUserMarketingActivitySynclogService()
    {
        return $this->createService('Marketing:UserMarketingActivitySynclogService');
    }
}
