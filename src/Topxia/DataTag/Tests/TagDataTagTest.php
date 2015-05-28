<?php

namespace Topxia\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\DataTag\TagDataTag;

class TagDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
        $datatag = new TagDataTag();
        $datatag->getData(array('tagId' => 1));

    }

}