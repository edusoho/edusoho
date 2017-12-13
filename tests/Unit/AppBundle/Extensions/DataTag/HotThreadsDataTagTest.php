<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\HotThreadsDataTag;

class HotThreadsDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $datatag = new HotThreadsDataTag();
        $result = $datatag->getData(array('count' => 5));
        var_dump($result);
    }
}
