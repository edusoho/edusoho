<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\RecommendCourseSetsDataTag;

class RecommendCourseSetsDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCountMissing()
    {
        $datatag = new RecommendCourseSetsDataTag();
        $datatag->getData(array());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCountError()
    {
        $datatag = new RecommendCourseSetsDataTag();
        $datatag->getData(array('count' => 101));
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

        $courseSet1 = $this->getCourseSetService()->createCourseSet(array('type' => 'normal', 'title' => 'course set1 title'));
        $this->getCourseSetService()->publishCourseSet($courseSet1['id']);
        $this->getCourseSetService()->recommendCourse($courseSet1['id'], 1);

        $courseSet2 = $this->getCourseSetService()->createCourseSet(array('type' => 'normal', 'title' => 'course set2 title'));
        $this->getCourseSetService()->publishCourseSet($courseSet2['id']);
        $this->getCourseSetService()->recommendCourse($courseSet2['id'], 2);

        $courseSet3 = $this->getCourseSetService()->createCourseSet(array('type' => 'normal', 'title' => 'course set3 title'));
        $this->getCourseSetService()->publishCourseSet($courseSet3['id']);
        $this->getCourseSetService()->updateCourseSet($courseSet3['id'], array('title' => 'course set3 title', 'categoryId' => $category['id'], 'serializeMode' => 'none', 'tags' => ''));
        $this->getCourseSetService()->recommendCourse($courseSet3['id'], 2);

        $courseSet4 = $this->getCourseSetService()->createCourseSet(array('type' => 'normal', 'title' => 'course set4 title'));

        $datatag = new RecommendCourseSetsDataTag();
        $courseSets = $datatag->getData(array('count' => 2));
        $this->assertEquals(2, count($courseSets));

        $courseSets = $datatag->getData(array('count' => 5, 'notFill' => 1));
        $this->assertEquals(3, count($courseSets));

        $courseSets = $datatag->getData(array('count' => 2, 'categoryId' => $category['id']));
        $this->assertEquals(1, count($courseSets));

        $courseSets = $datatag->getData(array('count' => 2, 'categoryCode' => $category['code']));
        $this->assertEquals(1, count($courseSets));

        $courseSets = $datatag->getData(array('count' => 2, 'categoryCode' => $category['code']));
        $this->assertEquals(1, count($courseSets));

        $courseSets = $datatag->getData(array('count' => 2, 'type' => 'live', 'orderBy' => 'recommended'));
        $this->assertEquals(0, count($courseSets));
    }

    private function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }
}
