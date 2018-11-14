<?php

namespace Tests\Unit\User\Register;

use Biz\BaseTestCase;
use AppBundle\Common\ReflectionUtils;

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
     * @expectedException \Biz\User\UserException
     */
    public function testRegisterWithInvalidNickname()
    {
        $registration = array(
            'email' => 'hello@howzhi.com',
            'nickname' => '123',
            'password' => '123',
        );
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
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testRegisterWithInvalidIdCard()
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
    public function testRegisterWithInvalidTruename()
    {
        $registration = array(
            'email' => 'hello@howzhi.com',
            'nickname' => '测试管理员123',
            'idcard' => '2123',
            'truename' => 'tom',
        );
        $this->getEmailRegistDecoder()->register($registration, 'default');
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testRegisterWithInvalidEmail()
    {
        $registration = array(
            'email' => 'hello',
            'nickname' => '测试管理员123',
            'idcard' => '2123',
            'truename' => 'tom',
        );
        $this->getEmailRegistDecoder()->register($registration, 'default');
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testRegisterWithExistedEmail()
    {
        $this->testRegistWithHappyPath();
        $registration = array(
            'email' => 'hello@howzhi.com',
            'nickname' => '123123',
            'idcard' => '1231123',
            'truename' => '陈列',
        );
        $this->getEmailRegistDecoder()->register($registration);
    }

    public function testGeneratePartnerAuthUserWithDiscuzRegister()
    {
        $authService = $this->mockBiz(
            'User:AuthService',
            array(
                array(
                    'functionName' => 'hasPartnerAuth',
                    'returnValue' => true,
                ),
            )
        );
        $registration = array(
            'email' => 'hello@howzhi.com',
            'nickname' => 'hello',
            'password' => '123',
            'token' => array(
                'userId' => 1357,
            ),
        );

        $result = ReflectionUtils::invokeMethod(
            $this->getEmailRegistDecoder(),
            'generatePartnerAuthUser',
            array($registration)
        );

        $this->assertEquals(1357, $result['partnerAuthUser']['id']);
        $this->assertEquals('discuz', $result['type']);
        $authService->shouldHaveReceived('hasPartnerAuth')->times(1);
    }

    public function testRegisterWithDiscuz()
    {
        $provider = $this->mockBiz(
            'authProvider',
            array(
                array(
                    'functionName' => 'register',
                    'returnValue' => array('id' => 1112222),
                ),
            )
        );
        $authService = $this->mockBiz(
            'User:AuthService',
            array(
                array(
                    'functionName' => 'hasPartnerAuth',
                    'returnValue' => true,
                ),
                array(
                    'functionName' => 'getPartnerName',
                    'returnValue' => 'discuz',
                ),
                array(
                    'functionName' => 'getAuthProvider',
                    'returnValue' => $provider,
                ),
            )
        );
        $registration = array(
            'email' => 'hello@howzhi.com',
            'nickname' => 'hello',
            'password' => '123',
        );
        $this->getEmailRegistDecoder()->register($registration);

        $provider->shouldHaveReceived('register')->times(1);
        $authService->shouldHaveReceived('hasPartnerAuth')->times(1);
        $authService->shouldHaveReceived('getPartnerName')->times(1);
        $authService->shouldHaveReceived('getAuthProvider')->times(1);

        $userBind = $this->getUserBindDao()->getByTypeAndFromId('discuz', '1112222');
        $this->assertEquals('1112222', $userBind['fromId']);
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
