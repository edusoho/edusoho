<?php

namespace Tests\Unit\Content\Type;

use Biz\BaseTestCase;
use Biz\Content\Type\ActivityContentType;
use AppBundle\Common\ReflectionUtils;

class ActivityContentTypeTest extends BaseTestCase
{
    public function testBasicFields()
    {
        $type = new ActivityContentType();
        $this->assertArrayEquals(
            array('title', 'body', 'picture', 'categoryId', 'tagIds'),
            $type->getBasicFields()
        );
    }

    public function testGetExtendedFields()
    {
        $type = new ActivityContentType();
        $this->assertArrayEquals(
            array(
                'field1' => 'startTime',
                'field2' => 'endTime',
                'field3' => 'location',
            ),
            $type->getExtendedFields()
        );
    }

    public function testGetAlias()
    {
        $type = new ActivityContentType();
        $this->assertEquals('activity', $type->getAlias());
    }

    public function testGetName()
    {
        $type = new ActivityContentType();
        $this->assertEquals('活动', $type->getName());
    }

    public function testGetKernel()
    {
        $type = new ActivityContentType();
        $kernel = ReflectionUtils::invokeMethod($type, 'getKernel');
        $this->assertEquals('Topxia\Service\Common\ServiceKernel', get_class($kernel));
    }
}
