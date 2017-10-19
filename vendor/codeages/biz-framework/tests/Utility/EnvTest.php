<?php

namespace Tests\Utility;

use PHPUnit\Framework\TestCase;
use Codeages\Biz\Framework\Utility\Env;

class EnvTest extends TestCase
{
    public function testGet()
    {
        putenv('foo1=bar');
        putenv('foo2=true');
        putenv('foo3=false');

        $this->assertEquals(Env::get('foo1'), 'bar');
        $this->assertTrue(Env::get('foo2'));
        $this->assertFalse(Env::get('foo3'));

        $this->assertEquals(Env::get('not_exist_key', 'default value'), 'default value');
    }

    public function testLoad()
    {
        $env = array(
            'foo1' => 'bar',
            'foo2' => true,
            'foo3' => false,
            'foo4' => 'true',
            'foo5' => 'false',
        );

        Env::load($env);

        $this->assertEquals(Env::get('foo1'), 'bar');
        $this->assertTrue(Env::get('foo2'));
        $this->assertFalse(Env::get('foo3'));
        $this->assertTrue(Env::get('foo4'));
        $this->assertFalse(Env::get('foo5'));
    }
}
