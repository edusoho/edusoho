<?php
class UnitTest extends PHPUnit_Framework_TestCase
{

    public function testOne()
    {
        exec("nginx -v", $output);
        $this->assertEquals(1, 1);
    }
}
