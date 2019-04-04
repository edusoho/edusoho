<?php

namespace Tests\Unit\AppBundle\Common\Tests;

use AppBundle\Common\MessageToolkit;
use Biz\BaseTestCase;

class MessageToolkitTest extends BaseTestCase
{
    public function testUnknownMessage()
    {
        $unKnownMessage = 'abcdefg';

        $result = MessageToolkit::convertMessageToKey($unKnownMessage);
        $expected = 'exception.common_error';

        $this->assertEquals($expected, $result);
    }

    public function testEduCloudUserDisableMessage()
    {
        $eduCloudUserDisableMessage = 'User is disabled.';

        $result = MessageToolkit::convertMessageToKey($eduCloudUserDisableMessage);
        $expected = 'exception.educloud.user_disabled_hint';

        $this->assertEquals($expected, $result);
    }
}
