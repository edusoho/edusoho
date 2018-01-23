<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\OpenCourseDataTag;

class OpenCourseDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $this->mockBiz('Taxonomy:CategoryService', array(
            array(
                'functionName' => 'getCategory',
                'returnValue' => array('id' => 1, 'name' => 'category name'),
            ),
        ));

        $course = $this->getOpenCourseService()->createCourse(array('title' => 'open course title', 'type' => 'open', 'about' => 'open course about', 'categoryId' => 1));

        $dataTag = new OpenCourseDataTag();
        
        $result = $dataTag->getData(array('courseId' => $course['id']));
        $this->assertEquals($course['id'], $result['id']);
        $this->assertArrayHasKey('teachers', $result);
        $this->assertArrayHasKey('category', $result);
    }

    
    protected function getOpenCourseService()
    {
        return $this->createService('OpenCourse:OpenCourseService');
    }
}
