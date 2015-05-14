<?php

namespace Topxia\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\DataTag\LatestUsersDataTag;

class LatestUsersDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
        $datatag = new LatestUsersDataTag();
        $datatag->getData(array('count' => 5));

    }

}