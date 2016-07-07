<?php

/**
 * @author Paul Annesley
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Flexihash_HasherTest extends PHPUnit_Framework_TestCase
{
    public function testCrc32Hash()
    {
        $hasher = new Flexihash_Crc32Hasher();
        $result1 = $hasher->hash('test');
        $result2 = $hasher->hash('test');
        $result3 = $hasher->hash('different');

        $this->assertEquals($result1, $result2);
        $this->assertNotEquals($result1, $result3); // fragile but worthwhile
    }

    public function testMd5Hash()
    {
        $hasher = new Flexihash_Md5Hasher();
        $result1 = $hasher->hash('test');
        $result2 = $hasher->hash('test');
        $result3 = $hasher->hash('different');

        $this->assertEquals($result1, $result2);
        $this->assertNotEquals($result1, $result3); // fragile but worthwhile
    }
}
