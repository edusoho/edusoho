<?php

namespace Topxia\WebBundle\Extensions\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\WebBundle\Extensions\DataTag\RecommendClassroomsDataTag;

class RecommendClassroomsDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
        $datatag = new RecommendClassroomsDataTag();
        $datatag->getData(array('count' => 5));

    }

}