<?php

namespace Tests\Unit\ApiBundle\Api\Resource;

use ApiBundle\Api\PathMeta;
use ApiBundle\Api\Resource\ResourceManager;
use Codeages\Biz\Framework\Context\Biz;
use ApiBundle\Api\Resource\Course\Course;

class ResourceManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $pathMeta = new PathMeta();
        $pathMeta->addResName('course');

        $resManager = new ResourceManager(new Biz());
        $this->assertTrue($resManager->create($pathMeta) instanceof Course);
    }
}