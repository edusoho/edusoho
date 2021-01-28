<?php

namespace Tests\Unit\User\Register;

use Biz\BaseTestCase;

class RegisterTypeToolkitTest extends BaseTestCase
{
    public function testGetRegisterTypes()
    {
        $registrations = array(
            'verifiedMobile' => '13766221231',
            'email' => 'acb@howzhi.com',
            'type' => 'qq',
            'distributorToken' => 'acd',
        );

        $result = $this->biz['user.register.type.toolkit']->getRegisterTypes($registrations);

        $this->assertArrayEquals(array('mobile', 'email', 'binder', 'distributor'), $result);
    }

    public function testGetThirdPartyRegisterTypes()
    {
        $registrations = array('distributorToken' => '1dds');
        $result = $this->biz['user.register.type.toolkit']->getThirdPartyRegisterTypes('mobile', $registrations);

        $this->assertArrayEquals(array('mobile', 'binder', 'distributor'), $result);
    }
}
