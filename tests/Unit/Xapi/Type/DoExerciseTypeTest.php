<?php

namespace Tests\Unit\Xapi\Type;

use Biz\BaseTestCase;

class DoExerciseTypeTest extends BaseTestCase
{
    public function testPackage()
    {
        $testpaperService = $this->mockBiz('Testpaper:TestpaperService',
            array(
                array(
                    'functionName' => 'getTestpaperResult',
                    'withParams' => array(),
                    'returnValue'
                ),
            )
        );
    }

    public function testPackages()
    {

    }
}