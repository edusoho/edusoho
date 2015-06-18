<?php

namespace Topxia\WebBundle\Extensions\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\WebBundle\Extensions\DataTag\VipLevelsDataTag;

class VipLevelsDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
        $datatag = new VipLevelsDataTag();
        $datatag->getData(array('count' => 5));

    }

}