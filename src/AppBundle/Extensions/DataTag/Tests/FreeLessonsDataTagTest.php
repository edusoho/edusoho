<?php

namespace AppBundle\Extensions\DataTag\Test;

use Biz\BaseTestCase;;
use AppBundle\Extensions\DataTag\FreeLessonsDataTag;

class FreeLessonsDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
        $datatag = new FreeLessonsDataTag();
        $datatag->getData(array('count' => 5));

    }

}