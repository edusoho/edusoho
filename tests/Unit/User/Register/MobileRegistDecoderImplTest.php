<?php

namespace Tests\Unit\User\Register;

use Biz\BaseTestCase;
use AppBundle\Common\ReflectionUtils;

class MobileRegistDecoderImplTest extends BaseTestCase
{
    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testWithInvalidSetting()
    {
        ReflectionUtils::invokeMethod(
            $this->getMobileRegistDecoder(),
            'validateBeforeSave',
            array(array('mobile' => 'dss'), 'default')
        );
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testWithExistedMobile()
    {
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'isMobileAvaliable',
                    'returnValue' => false,
                ),
            )
        );
        ReflectionUtils::invokeMethod(
            $this->getMobileRegistDecoder(),
            'validateBeforeSave',
            array(array('mobile' => '13675662211'))
        );
    }

    protected function getMobileRegistDecoder()
    {
        return $this->biz['user.register.mobile'];
    }

    protected function getUserService()
    {
        return $this->biz->service['User:UserService'];
    }
}
