<?php

namespace Topxia\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\DataTag\VipLevelsDataTag;

class VipLevelsDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
        $datatag = new VipLevelsDataTag();
        $datatag->getData(array('count' => 5));

    }

}