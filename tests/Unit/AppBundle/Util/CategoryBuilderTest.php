<?php

namespace Tests\Unit\AppBundle\Util;

use Biz\BaseTestCase;
use AppBundle\Util\CategoryBuilder;

class CategoryBuilderTest extends BaseTestCase
{
    public function testBuildChoices()
    {
        $builder = new CategoryBuilder();
        $result = $builder->buildChoices('xxx');
        $this->assertEmpty($result);

        $result = $builder->buildChoices('course');
        $this->assertNotNull($result);
    }
}
