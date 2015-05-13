<?php

namespace Topxia\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\DataTag\UserandProfilesDataTag;

class UserandProfilesDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
        $datatag = new UserandProfilesDataTag();
        $datatag->getData(array('userId' => 1));

    }

}