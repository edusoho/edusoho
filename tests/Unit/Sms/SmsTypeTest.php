<?php

namespace Tests\Unit\Sms;

use Biz\BaseTestCase;
use Biz\Sms\SmsType;

class SmsTypeTest extends BaseTestCase
{
    public function testSmsType()
    {
        $result = SmsType::IMPORT_USER;

        $this->assertEquals(1738, $result);
    }
}
