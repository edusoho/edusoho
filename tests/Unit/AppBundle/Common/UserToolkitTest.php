<?php

namespace Tests\Unit\AppBundle\Common;

use AppBundle\Common\UserToolkit;
use Biz\BaseTestCase;

class UserToolkitTest extends BaseTestCase
{
    public function testIsEmailGeneratedByStstem()
    {
        $this->assertFalse(UserToolkit::isEmailGeneratedBySystem(''));
        $this->assertFalse(UserToolkit::isEmailGeneratedBySystem('test@test.com'));
        $this->assertTrue(UserToolkit::isEmailGeneratedBySystem('test@edusoho.net'));
    }

    public function testIsGenderDefault()
    {
        $this->assertFalse(UserToolkit::isGenderDefault(''));
        $this->assertFalse(UserToolkit::isGenderDefault('default'));
        $this->assertTrue(UserToolkit::isGenderDefault('secret'));
    }
}
