<?php

namespace Tests\Unit\AppBundle\Common;

use AppBundle\Common\ClassroomToolkit;
use Biz\BaseTestCase;

class ClassroomToolkitTest extends BaseTestCase
{
    public function testBuildMemberDeadline()
    {
        $expiryDate = array(
            'expiryMode' => 'days',
            'expiryValue' => 30,
        );

        $result = ClassroomToolkit::buildMemberDeadline($expiryDate);

        $expected = time() + 30 * 24 * 60 * 60 - $result;

        $this->assertTrue(in_array($expected, range(0, 5)));
    }

    /**
     * @expectedException \AppBundle\Common\Exception\UnexpectedValueException
     * @expectedExceptionMessage 有效期的设置时间小于当前时间！
     */
    public function testBuildMemberDeadlineWithException()
    {
        $expiryDate = array(
            'expiryMode' => 'date',
            'expiryValue' => time() - 60,
        );

        ClassroomToolkit::buildMemberDeadline($expiryDate);
    }
}
