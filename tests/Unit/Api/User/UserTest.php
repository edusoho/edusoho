<?php

namespace Tests\Unit\Api\Setting;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\User\User;
use Biz\BaseTestCase;
use Symfony\Component\DependencyInjection\Container;

class UserTest extends BaseTestCase
{
    public function testGetUser()
    {
        $actual = array(
            array('id', 1, 'getUser'),
            array('email', 'test@edusoho.com', 'getUserByEmail'),
            array('mobile', '139250312345', 'getUserByVerifiedMobile'),
            array('nickname', 'test', 'getUserByNickname'),
        );

        $expected = array(
            'id' => 1,
            'nickname' => 'test',
            'title' => 'hellp',
            'avatar' => array(),
        );

        foreach ($actual as $key => $act) {

            $this->mockBiz('User:UserService', array(
                array('functionName' => $act[2], 'returnValue' => $expected, 'withParams' => array($act[1]))
            ));

            $api = new User(new Container(), $this->getBiz());
            $apiRequest = new ApiRequest('', '', array('identifyType' => $act[0]));
            $result = $api->get($apiRequest, $act[1]);

            $this->assertEquals($expected, $result);
        }
    }
}
