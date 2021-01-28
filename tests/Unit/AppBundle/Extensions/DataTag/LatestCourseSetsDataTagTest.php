<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\LatestCourseSetsDataTag;

class LatestCourseSetsDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testArgumentError()
    {
        $dataTag = new LatestCourseSetsDataTag();
        $dataTag->getData(array());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCountGTError()
    {
        $dataTag = new LatestCourseSetsDataTag();
        $dataTag->getData(array('count' => 101));
    }

    public function testGetData()
    {
        $this->mockBiz('Taxonomy:CategoryGroupDao', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('id' => 1),
            ),
        ));

        $category = $this->getCategoryService()->createCategory(array('name' => 'category name', 'code' => 'courseSet', 'groupId' => 1, 'parentId' => 0, 'published' => 1));

        $courseSet = $this->getCourseSetService()->createCourseSet(array('type' => 'normal', 'title' => 'course set1 title'));
        $courseSet = $this->getCourseSetService()->updateCourseSet($courseSet['id'], array('title' => 'course set3 title', 'categoryId' => $category['id'], 'serializeMode' => 'none', 'tags' => ''));
        $this->getCourseSetService()->publishCourseSet($courseSet['id']);

        $courseSet2 = $this->getCourseSetService()->createCourseSet(array('type' => 'normal', 'title' => 'course set2 title'));
        $this->getCourseSetService()->publishCourseSet($courseSet2['id']);

        $courseSet3 = $this->getCourseSetService()->createCourseSet(array('type' => 'normal', 'title' => 'course set3 title'));
        $this->getCourseSetService()->publishCourseSet($courseSet3['id']);
        $this->getCourseSetService()->recommendCourse($courseSet3['id'], 5);

        $courseSet4 = $this->getCourseSetService()->createCourseSet(array('type' => 'normal', 'title' => 'course set4 title'));

        $datatag = new LatestCourseSetsDataTag();

        $data = $datatag->getData(array('count' => 2));
        $this->assertEquals(2, count($data));

        $data = $datatag->getData(array('count' => 2, 'categoryId' => $category['id']));
        $this->assertEquals(1, count($data));

        $data = $datatag->getData(array('count' => 2, 'categoryCode' => $category['code']));
        $this->assertEquals(1, count($data));

        $data = $datatag->getData(array('count' => 2, 'orderBy' => 'recommended'));
        $this->assertEquals(2, count($data));
        $this->assertEquals($courseSet3['id'], $data[0]['id']);

        $data = $datatag->getData(array('count' => 2, 'notFree' => 1));
        $this->assertEquals(0, count($data));
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }
}
