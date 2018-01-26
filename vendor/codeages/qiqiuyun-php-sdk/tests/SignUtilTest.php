<?php

namespace QiQiuYun\SDK\Tests;

use PHPUnit\Framework\TestCase;
use QiQiuYun\SDK\Util\SignUtil;

class SignUtilTest extends TestCase
{
    public function testSerialize()
    {
        $result = SignUtil::serialize(array('id' => 123, 'de' => 222, 'fh' => 221));
        $exepctedResult = json_encode(
            array(
                'de' => 222,
                'fh' => 221,
                'id' => 123,
            )
        );

        $this->assertEquals($exepctedResult, $result);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSerializeWithoutArray()
    {
        $result = SignUtil::serialize(1231);
    }
}
