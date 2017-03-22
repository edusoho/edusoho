<?php

namespace Tests\Unit\ApiBundle\Api\Resource;

use ApiBundle\Api\PathMeta;
use ApiBundle\Api\Resource\CourseSet\CourseSet;
use ApiBundle\Api\Resource\ResourceManager;
use ApiBundle\Api\Resource\ResourceProxy;
use Codeages\Biz\Framework\Context\Biz;

class ResourceManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $pathMeta = new PathMeta();
        $pathMeta->addResName('course_sets');

        $resManager = new ResourceManager(new Biz());
        $resProxy = $resManager->create($pathMeta);
        $this->assertTrue($resProxy instanceof ResourceProxy);
        $this->assertTrue($resProxy->getResource() instanceof CourseSet);
    }
}