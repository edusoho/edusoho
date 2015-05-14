<?php

namespace Topxia\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\DataTag\TeacherCoursesDataTag;

class TeacherCoursesDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
        $datatag = new TeacherCoursesDataTag();
        $datatag->getData(array('userId' =>1, 'count' => 5));

    }

}