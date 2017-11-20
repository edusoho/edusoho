<?php

namespace Tests\Unit\User;

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

    protected function getMobileRegistDecoder()
    {
        return $this->biz['user.register.mobile'];
    }
}
