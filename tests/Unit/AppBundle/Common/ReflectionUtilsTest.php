<?php

namespace Tests\Unit\AppBundle\Common;

use Biz\BaseTestCase;
use AppBundle\Common\ReflectionUtils;
use Tests\Unit\AppBundle\Common\Tool\ReflectionTester;

class ReflectionUtilsTest extends BaseTestCase
{
    public function testInvokeMethod()
    {
        $result = ReflectionUtils::invokeMethod(new ReflectionTester(), 'getHello', array('abc', 'ddd'));
        $this->assertEquals('hello_abc_ddd', $result);
    }

    public function testSetProperty()
    {
        $tester = new ReflectionTester();
        $result = ReflectionUtils::setProperty($tester, 'ok', true);
        $this->assertEquals(true, $result->getOk());
    }

    public function testGetProperty()
    {
        $tester = new ReflectionTester();
        $tester->setOk(true);
        $result = ReflectionUtils::getProperty($tester, 'ok');
        $this->assertEquals(true, $result);
    }

    public function testSetStaticProperty()
    {
        $tester = new ReflectionTester();
        ReflectionUtils::setStaticProperty($tester, 'staticAttr', 'static');
        $this->assertEquals('static', ReflectionTester::getStaticAttr());
    }
}
