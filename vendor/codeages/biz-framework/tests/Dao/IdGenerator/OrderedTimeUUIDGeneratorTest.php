<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Codeages\Biz\Framework\Dao\IdGenerator\OrderedTimeUUIDGenerator;

/**
 * @requires PHP 5.5
 */
class OrderedTimeUUIDGeneratorTest extends TestCase
{
    public function testGenerate()
    {
        $generator = new OrderedTimeUUIDGenerator();
        $id = $generator->generate();
        $this->assertEquals(16, strlen($id));

        $id = $generator->generate(false);
        $this->assertEquals(36, strlen($id));
    }

    public function testEncode()
    {
        $generator = new OrderedTimeUUIDGenerator();
        $id = $generator->generate(false);

        $this->assertEquals(16, strlen($generator->encode($id)));
    }

    public function testDecode()
    {
        // 0fe8f12e-1066-11e8-918f-a45e60d64c23
        $generator = new OrderedTimeUUIDGenerator();
        $id = $generator->generate();

        $this->assertEquals(36, strlen($generator->decode($id)));
    }
}