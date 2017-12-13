<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\HotGroupDataTag;

class HotGroupDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $datatag = new HotGroupDataTag();
        $datatag->getData(array('count' => 5));
    }
}
