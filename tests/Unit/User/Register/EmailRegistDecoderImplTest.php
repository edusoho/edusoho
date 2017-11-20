<?php

namespace Tests\Unit\User;

use Biz\BaseTestCase;

class EmailRegistDecoderImplTest extends BaseTestCase
{
    public function testRegistWithHappyPath()
    {
        $registration = array(
            'email' => 'hello@howzhi.com',
            'nickname' => 'hello',
            'password' => '123',
        );
        list($user, $inviteUser) = $this->getEmailRegistDecoder()->register($registration, 'default');

        $user = $this->getUserService()->getUser($user['id']);

        $this->assertEquals('hello@howzhi.com', $user['email']);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testRegisterNicknameValidateFailed()
    {
        $registration = array(
            'email' => 'hello@howzhi.com',
            'nickname' => '123',
            'password' => '123',
        );
        $this->getEmailRegistDecoder()->register($registration, 'default');
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testRegisterNicknameExisted()
    {
        $this->testRegistWithHappyPath();
        $this->testRegistWithHappyPath();
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testRegisterIdCardValidateFailed()
    {
        $registration = array(
            'email' => 'hello@howzhi.com',
            'nickname' => '测试管理员123',
            'idcard' => '!@#',
        );
        $this->getEmailRegistDecoder()->register($registration, 'default');
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testRegisterTruenameValidateFailed()
    {
        $registration = array(
            'email' => 'hello@howzhi.com',
            'nickname' => '测试管理员123',
            'idcard' => '2123',
            'truename' => 'tom',
        );
        $this->getEmailRegistDecoder()->register($registration, 'default');
    }

    protected function getEmailRegistDecoder()
    {
        return $this->biz['user.register.email'];
    }

    protected function getUserService()
    {
        return $this->biz->dao('User:UserService');
    }
}
