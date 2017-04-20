<?php

namespace AppBundle\Extensions\DataTag\Test;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\HotThreadsDataTag;

class HotThreadsDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $datatag = new HotThreadsDataTag();
        $datatag->getData(array('count' => 5));
    }
}
