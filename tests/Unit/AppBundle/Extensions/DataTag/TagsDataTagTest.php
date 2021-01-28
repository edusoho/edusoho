<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\TagsDataTag;

class TagsDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $tag1 = $this->getTagService()->addTag(array('name' => 'tag1'));
        $tag2 = $this->getTagService()->addTag(array('name' => 'tag2'));
        $tag3 = $this->getTagService()->addTag(array('name' => 'tag3'));
        $datatag = new TagsDataTag();
        $tags = $datatag->getData(array('count' => 5));
        $this->assertEquals(3, count($tags));
    }

    public function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy:TagService');
    }
}
