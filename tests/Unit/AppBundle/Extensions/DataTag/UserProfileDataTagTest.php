<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\UserProfileDataTag;
use Biz\BaseTestCase;

class UserProfileDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEmptyUserId()
    {
        $dataTag = new UserProfileDataTag();
        $dataTag->getData(array());
    }

    public function testGetData()
    {
        $dataTag = new UserProfileDataTag();

        $this->mockBiz('User:UserService', array(
            array(
                'functionName' => 'getUserProfile',
                'returnValue' => array('id' => 1, 'mobile' => '15205050505'),
            ),
        ));

        $userProfile = $dataTag->getData(array('userId' => 1));
        $this->assertEquals(1, $userProfile['id']);
        $this->assertEquals('15205050505', $userProfile['mobile']);
    }
}
