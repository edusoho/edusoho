<?php

namespace Topxia\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\DataTag\FreeLessonsDataTag;

class FreeLessonsDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
        $datatag = new FreeLessonsDataTag();
        $datatag->getData(array('count' => 5));

    }

}