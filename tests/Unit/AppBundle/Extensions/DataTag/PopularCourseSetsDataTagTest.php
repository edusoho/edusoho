<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\PopularCourseSetsDataTag;

class PopularCourseSetsDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testArgumentMissing()
    {
        $datatag = new PopularCourseSetsDataTag();
        $datatag->getData(array());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testArgumentError()
    {
        $datatag = new PopularCourseSetsDataTag();
        $datatag->getData(array('count' => 101));
    }

    public function testGetDataHasCategoryId()
    {
        $this->mockBiz('Taxonomy:CategoryGroupDao', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('id' => 1),
            ),
        ));

        $category = $this->getCategoryService()->createCategory(array('name' => 'category name', 'code' => 'courseSet', 'groupId' => 1, 'parentId' => 0, 'published' => 1));

        $courseSet = $this->getCourseSetService()->createCourseSet(array('type' => 'normal', 'title' => 'course set1 title'));
        $this->getCourseSetService()->publishCourseSet($courseSet['id']);
        $courseSet = $this->getCourseSetService()->updateCourseSet($courseSet['id'], array('title' => 'course set1 title', 'categoryId' => $category['id'], 'serializeMode' => 'none', 'tags' => ''));

        $dataTag = new PopularCourseSetsDataTag();
        $data = $dataTag->getData(array('count' => 5, 'categoryId' => $category['id']));

        $this->assertEquals(1, count($data));
    }

    public function testGetDataHasType()
    {
        $courseSet1 = $this->getCourseSetService()->createCourseSet(array('type' => 'normal', 'title' => 'course set1 title'));
        $this->getCourseSetService()->publishCourseSet($courseSet1['id']);
        $courseSet1 = $this->getCourseSetService()->recommendCourse($courseSet1['id'], 10);

        $courseSet2 = $this->getCourseSetService()->createCourseSet(array('type' => 'normal', 'title' => 'course set2 title'));
        $this->getCourseSetService()->publishCourseSet($courseSet2['id']);

        $dataTag = new PopularCourseSetsDataTag();
        $data = $dataTag->getData(array('count' => 5, 'type' => 'recommended'));

        $this->assertEquals(1, count($data));
    }

    public function testGetDataHasPrice()
    {
        $courseSet = $this->getCourseSetService()->createCourseSet(array('type' => 'normal', 'title' => 'course set1 title'));
        $this->getCourseSetService()->publishCourseSet($courseSet['id']);

        $dataTag = new PopularCourseSetsDataTag();
        $data = $dataTag->getData(array('count' => 5, 'price' => 1));

        $this->assertEmpty($data);
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }
}
