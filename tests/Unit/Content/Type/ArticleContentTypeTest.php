<?php

namespace Tests\Unit\Content\Type;

use Biz\BaseTestCase;
use Biz\Content\Type\ArticleContentType;
use AppBundle\Common\ReflectionUtils;

class ArticleContentTypeTest extends BaseTestCase
{
    public function testBasicFields()
    {
        $type = new ArticleContentType();
        $this->assertArrayEquals(
            array('title', 'body', 'picture', 'categoryId', 'tagIds'),
            $type->getBasicFields()
        );
    }

    public function testGetAlias()
    {
        $type = new ArticleContentType();
        $this->assertEquals('article', $type->getAlias());
    }

    public function testGetName()
    {
        $type = new ArticleContentType();
        $this->assertEquals('文章', $type->getName());
    }

    public function testGetKernel()
    {
        $type = new ArticleContentType();
        $kernel = ReflectionUtils::invokeMethod($type, 'getKernel');
        $this->assertEquals('Topxia\Service\Common\ServiceKernel', get_class($kernel));
    }
}
