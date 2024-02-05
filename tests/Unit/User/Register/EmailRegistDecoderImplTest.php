<?php

namespace Tests\Unit\User\Register;

use Biz\BaseTestCase;

class EmailRegistDecoderImplTest extends BaseTestCase
{
    public function testRegistWithHappyPath()
    {
        $registration = [
            'email' => 'hello@howzhi.com',
            'nickname' => 'hello',
            'password' => '123456',
        ];
        list($user, $inviteUser) = $this->getEmailRegistDecoder()->register($registration, 'default');

        $user = $this->getUserService()->getUser($user['id']);

        $this->assertEquals('hello@howzhi.com', $user['email']);
    }

    /**
     * @expectedException \Biz\User\UserException
     */
    public function testRegisterWithInvalidNickname()
    {
        $registration = [
            'email' => 'hello@howzhi.com',
            'nickname' => '123',
            'password' => '123456',
        ];
        $this->getEmailRegistDecoder()->register($registration, 'default');
    }

    /**
     * @expectedException \Biz\User\UserException
     */
    public function testRegisterWithExistedNickname()
    {
        $this->testRegistWithHappyPath();
        $this->testRegistWithHappyPath();
    }

    /**
     * @expectedException \Biz\User\UserException
     */
    public function testRegisterWithInvalidIdCard()
    {
        $registration = [
            'email' => 'hello@howzhi.com',
            'nickname' => '测试管理员123',
            'idcard' => '!@#',
        ];
        $this->getEmailRegistDecoder()->register($registration, 'default');
    }

    /**
     * @expectedException \Biz\User\UserException
     */
    public function testRegisterWithInvalidTruename()
    {
        $registration = [
            'email' => 'hello@howzhi.com',
            'nickname' => '测试管理员123',
            'idcard' => '2123',
            'truename' => 'tom',
        ];
        $this->getEmailRegistDecoder()->register($registration, 'default');
    }

    /**
     * @expectedException \Biz\User\UserException
     */
    public function testRegisterWithInvalidEmail()
    {
        $registration = [
            'email' => 'hello',
            'nickname' => '测试管理员123',
            'idcard' => '2123',
            'truename' => 'tom',
        ];
        $this->getEmailRegistDecoder()->register($registration, 'default');
    }

    /**
     * @expectedException \Biz\User\UserException
     */
    public function testRegisterWithExistedEmail()
    {
        $this->testRegistWithHappyPath();
        $registration = [
            'email' => 'hello@howzhi.com',
            'nickname' => '123123',
            'idcard' => '1231123',
            'truename' => '陈列',
        ];
        $this->getEmailRegistDecoder()->register($registration);
    }

    protected function getEmailRegistDecoder()
    {
        return $this->biz['user.register.email'];
    }

    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    protected function getUserBindDao()
    {
        return $this->biz->service('User:UserBindDao');
    }
}
