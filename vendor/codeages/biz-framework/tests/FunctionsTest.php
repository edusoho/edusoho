<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class FunctionsTest extends TestCase
{
    public function testEnv()
    {
        putenv('foo1=bar');
        $this->assertEquals(getenv('foo1'), 'bar');
    }
}
