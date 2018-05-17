<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\TagMarksDataTag;

class TagMarksDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $this->mockBiz('Taxonomy:TagService', array(
            array(
                'functionName' => 'getTag',
                'withParams' => array(2),
                'returnValue' => array('id' => 2, 'name' => 'tag1 name', 'groupId' => 1),
            ),
            array(
                'functionName' => 'getTag',
                'withParams' => array(3),
                'returnValue' => array('id' => 3, 'name' => 'tag2 name', 'groupId' => 2),
            ),
        ));

        $datatag = new TagMarksDataTag();
        $data = $datatag->getData(array('tags' => array(1 => 2, 2 => 3)));

        $this->assertEquals(2, count($data));
        $this->assertArrayHasKey('tagName', $data[0]);
        $this->assertArrayHasKey('tagId', $data[0]);
        $this->assertArrayHasKey('groupId', $data[0]);
    }
}
