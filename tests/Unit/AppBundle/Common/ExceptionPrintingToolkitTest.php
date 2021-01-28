<?php

namespace AppBundle\Common\Tests;

use AppBundle\Common\ExceptionPrintingToolkit;
use Biz\BaseTestCase;

class ExceptionPrintingToolkitTest extends BaseTestCase
{
    public function testPrintTraceAsArray()
    {
        try {
            $a['id'] = $b['id'];
            $this->assertFalse(true);
        } catch (\Exception $e) {
            $result = ExceptionPrintingToolkit::printTraceAsArray($e);
            $msg = $result['previous'][0];
            $this->assertTrue(
                -1 != strstr($msg['message'], 'Undefined variable: b')
            );

            $this->assertTrue(
                -1 != strstr($msg['trace'][0], 'ExceptionPrintingToolkitTest.php line 13')
            );

            $this->assertTrue(
                -1 != strstr($msg['trace'][0], 'phpunit-6.4.4.phar line 566')
            );
        }
    }
}
