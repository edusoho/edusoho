<?php

namespace Tests\Unit\User;

use Biz\BaseTestCase;

class EmailRegistDecoderImplTest extends BaseTestCase
{
    public function testFullRegist()
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

    protected function getEmailRegistDecoder()
    {
        return $this->biz['user.register.email'];
    }

    protected function getUserService()
    {
        return $this->biz->dao('User:UserService');
    }
}