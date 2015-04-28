<?php

namespace Topxia\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\DataTag\HotGroupDataTag;

class HotGroupDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
        $datatag = new HotGroupDataTag();
        $datatag->getData(array('count' => 5));
    }

}