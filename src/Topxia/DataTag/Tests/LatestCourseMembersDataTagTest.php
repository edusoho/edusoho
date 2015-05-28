<?php

namespace Topxia\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\DataTag\LatestCourseMembersDataTag;

class LatestCourseMembersDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
        $datatag = new LatestCourseMembersDataTag();
        $datatag->getData(array('categoryId' => 1, 'count' => 5));

    }

}