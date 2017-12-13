<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\BlocksDataTag;

class BlocksDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $datatag = new BlocksDataTag();
        $contents = $datatag->getData(array('codes' => array('test1', 'test2')));

        $this->assertArrayHasKey('test1', $contents);
        $this->assertArrayHasKey('test2', $contents);
    }
}
