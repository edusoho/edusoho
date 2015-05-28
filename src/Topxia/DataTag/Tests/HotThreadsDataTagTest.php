<?php

namespace Topxia\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\DataTag\HotThreadsDataTag;

class HotThreadsDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
        $datatag = new HotThreadsDataTag();
        $datatag->getData(array('count' => 5));

    }

}