<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\CourseSetDataTag;

class CourseSetDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testArgumentError()
    {
        $datatag = new CourseSetDataTag();
        $datatag->getData(array());
    }

    public function testGetData()
    {
        $this->mockBiz('Taxonomy:CategoryService', array(
            array(
                'functionName' => 'getCategory',
                'returnValue' => array('id' => 3),
            ),
        ));
        $courseSet = $this->getCourseSetService()->createCourseSet(array('type' => 'normal', 'title' => 'course set1 title'));
        $courseSet = $this->getCourseSetService()->updateCourseSet($courseSet['id'], array('title' => 'course set3 title', 'categoryId' => 3, 'serializeMode' => 'none', 'tags' => ''));

        $datatag = new CourseSetDataTag();
        $data = $datatag->getData(array('id' => $courseSet['id']));

        $this->assertEquals($courseSet['id'], $data['id']);
        $this->assertEquals($courseSet['categoryId'], $data['category']['id']);
    }

    private function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }
}
