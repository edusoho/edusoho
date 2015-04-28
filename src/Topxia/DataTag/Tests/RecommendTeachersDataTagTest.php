<?php

namespace Topxia\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\DataTag\RecommendTeachersDataTag;

class RecommendTeachersDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
        $datatag = new RecommendTeachersDataTag();
        $datatag->getData(array('count' => 5));

    }

}