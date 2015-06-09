<?php

namespace Topxia\WebBundle\Extensions\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\WebBundle\Extensions\DataTag\BlocksDataTag;

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