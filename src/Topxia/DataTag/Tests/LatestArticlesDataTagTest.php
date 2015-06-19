<?php

namespace Topxia\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\DataTag\LatestArticlesDataTag;

class LatestArticlesDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
        $datatag = new LatestArticlesDataTag();
        $datatag->getData(array('count' => 5, 'type' => 'featured'));

    }

}