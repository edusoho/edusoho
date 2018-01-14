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
            $this->assertEquals(
                '1/1 PHPUnit\Framework\Error\Notice: Undefined variable: b',
                $msg['message']
            );

            $this->assertEquals(
                '1.  in /private/var/www/projects/edusoho_test/tests/Unit/AppBundle/Common/ExceptionPrintingToolkitTest.php line 13',
                $msg['trace'][0]
            );

            $this->assertEquals(
                '12. at PHPUnit\TextUI\Command::main(...args...) in /usr/local/Cellar/phpunit/6.4.4/libexec/phpunit-6.4.4.phar line 566',
                $msg['trace'][11]
            );
        }
    }
}
