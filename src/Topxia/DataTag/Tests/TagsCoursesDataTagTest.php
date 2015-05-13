<?php

namespace Topxia\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\DataTag\TagsCoursesDataTag;

class TagsCoursesDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
        $datatag = new TagsCoursesDataTag();
        $datatag->getData(array('count' => 5, 'tags' => array('tag1', 'tag2')));

    }

}