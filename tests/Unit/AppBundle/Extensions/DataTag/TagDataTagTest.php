<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\TagDataTag;

class TagDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $tag1 = $this->getTagService()->addTag(array('name' => 'tag1'));
        $tag2 = $this->getTagService()->addTag(array('name' => 'tag2'));
        $tag3 = $this->getTagService()->addTag(array('name' => 'tag3'));
        $datatag = new TagDataTag();
        $tags = $datatag->getData(array('tagId' => 1));
        $this->assertEquals($tag1['id'], $tags['id']);
        $tags = $datatag->getData(array('tagId' => 2));
        $this->assertEquals($tag2['id'], $tags['id']);
        $tags = $datatag->getData(array('tagId' => 3));
        $this->assertEquals($tag3['id'], $tags['id']);
    }

    public function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy:TagService');
    }
}
