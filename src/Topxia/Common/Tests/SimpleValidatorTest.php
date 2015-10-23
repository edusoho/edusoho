<?php

namespace Topxia\Common\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Common\ArrayToolkit;

class SimpleValidatorTest extends BaseTestCase
{
    public function testNull()
    {
        $this->assertNull(null);
    }
}