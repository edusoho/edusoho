<?php

namespace Tests\Unit\ItemBankExercise\ExpiryMode;

use Biz\BaseTestCase;
use Biz\ItemBankExercise\ExpiryMode\ExpiryModeFactory;

class ExpiryModeFactoryTest extends BaseTestCase
{
    public function testCreate()
    {
        $res = ExpiryModeFactory::create('forever');

        $this->assertNotEmpty($res);
    }
}
