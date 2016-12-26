<?php

namespace Flexihash\Tests\Hasher;

use PHPUnit_Framework_TestCase;
use Flexihash\Hasher\Crc32Hasher;
use Flexihash\Hasher\Md5Hasher;

/**
 * @author Paul Annesley
 * @license http://www.opensource.org/licenses/mit-license.php
 */
class HasherTest extends PHPUnit_Framework_TestCase
{
    public function testCrc32Hash()
    {
        $hasher = new Crc32Hasher();
        $result1 = $hasher->hash('test');
        $result2 = $hasher->hash('test');
        $result3 = $hasher->hash('different');

        $this->assertEquals($result1, $result2);
        $this->assertNotEquals($result1, $result3); // fragile but worthwhile
    }

    public function testMd5Hash()
    {
        $hasher = new Md5Hasher();
        $result1 = $hasher->hash('test');
        $result2 = $hasher->hash('test');
        $result3 = $hasher->hash('different');

        $this->assertEquals($result1, $result2);
        $this->assertNotEquals($result1, $result3); // fragile but worthwhile
    }
}
