<?php

namespace Topxia\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\DataTag\NavigationDataTag;

class NavigationDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
        $datatag = new NavigationDataTag();
        $datatag->getData(array('type' => 'top'));

    }

}