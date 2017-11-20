<?php

namespace Tests\Unit\User;

use Biz\BaseTestCase;
use AppBundle\Common\ReflectionUtils;

class BinderRegistDecoderImplTest extends BaseTestCase
{
    public function testRegistWithHappyPath()
    {
        $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('qq_set_fill_account' => true),
                    'withParams' => array('login_bind', array()),
                    'runTimes' => 1,
                )
            )
        );

        $registration = array(
            'email' => 'hello@howzhi.com',
            'nickname' => 'hello',
            'password' => '123',
            'token' => array('userId' => 1, 'token' => 'newToken'),
        );

        $binderDecoder = $this->getBinderRegistDecoder();
        $binderDecoder->setRegister($this->getEmailRegistDecoder());
        list($user, $inviteUser) = $binderDecoder->register($registration, 'qq');

        $user = $this->getUserService()->getUser($user['id']);

        $this->assertEquals('hello@howzhi.com', $user['email']);
        $this->assertEquals('', $user['salt']);
        $this->assertEquals('', $user['password']);
        $this->assertEquals(1, $user['setup']);

        $userBind = $this->getUserBindDao()->getByTypeAndFromId('qq', 1);
        $this->assertEquals(1, $userBind['fromId']);
        $this->assertEquals('newToken', $userBind['token']);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testWithInvalidSetting()
    {
        $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('qq_set_fill_account' => false),
                    'withParams' => array('login_bind', array()),
                    'runTimes' => 1,
                )
            )
        );
        ReflectionUtils::invokeMethod($this->getBinderRegistDecoder(), 'validateBeforeSave', array(array(), 'qq'));
    }

    protected function getEmailRegistDecoder()
    {
        return $this->biz['user.register.email'];
    }

    protected function getBinderRegistDecoder()
    {
        return $this->biz['user.register.binder'];
    }

    protected function getUserService()
    {
        return $this->biz->dao('User:UserService');
    }

    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    protected function getUserBindDao()
    {
        return $this->biz->service('User:UserBindDao');
    }
}
