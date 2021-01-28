<?php

namespace Tests\Unit\Activity\Type;

use AppBundle\Common\ReflectionUtils;

class DiscussTest extends BaseTypeTestCase
{
    const TYPE = 'discuss';

    public function testRegisterListeners()
    {
        $type = $this->getActivityConfig(self::TYPE);
        $return = ReflectionUtils::invokeMethod($type, 'registerListeners');

        $this->assertEquals(array(), $return);
    }
}
