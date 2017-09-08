<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Codeages\Biz\Framework\Utility\Env;

class FunctionsTest extends TestCase
{
    public function testEnv()
    {
        putenv('foo1=bar');
        $this->assertEquals(env('foo1'), 'bar');
    }
}