<?php

namespace Tests\Unit\Content\Type;

use Biz\BaseTestCase;
use Biz\Content\Type\PageContentType;
use AppBundle\Common\ReflectionUtils;

class PageContentTypeTest extends BaseTestCase
{
    public function testBasicFields()
    {
        $type = new PageContentType();
        $this->assertArrayEquals(
            array('title', 'body', 'picture', 'alias', 'template', 'editor'),
            $type->getBasicFields()
        );
    }

    public function testGetAlias()
    {
        $type = new PageContentType();
        $this->assertEquals('page', $type->getAlias());
    }

    public function testGetName()
    {
        $type = new PageContentType();
        $this->assertEquals('页面', $type->getName());
    }

    public function testGetKernel()
    {
        $type = new PageContentType();
        $kernel = ReflectionUtils::invokeMethod($type, 'getKernel');
        $this->assertEquals('Topxia\Service\Common\ServiceKernel', get_class($kernel));
    }
}
