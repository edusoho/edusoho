<?php

namespace AppBundle\Extensions\DataTag\Test;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\VipLevelsDataTag;

class VipLevelsDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $datatag = new VipLevelsDataTag();
        $datatag->getData(array('count' => 5));
    }
}
