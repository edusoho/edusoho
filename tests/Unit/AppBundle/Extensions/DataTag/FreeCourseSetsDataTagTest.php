<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\Announcement\Service\AnnouncementService;
use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\FreeCourseSetsDataTag;

class FreeCourseSetsDataTagTest extends BaseTestCase
{
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

        $courseSet2 = $this->getCourseSetService()->createCourseSet(array('type' => 'normal', 'title' => 'course set2 title'));
        $this->getCourseSetService()->publishCourseSet($courseSet2['id']);
        $this->getCourseSetService()->recommendCourse($courseSet2['id'], 5);

        $courseSet3 = $this->getCourseSetService()->createCourseSet(array('type' => 'normal', 'title' => 'course set3 title'));
        $this->getCourseSetService()->publishCourseSet($courseSet3['id']);
        $this->getCourseSetService()->updateCourseSet($courseSet3['id'], array('title' => 'course set3 title', 'categoryId' => $category['id'], 'serializeMode' => 'none', 'tags' => ''));

        $courseSet4 = $this->getCourseSetService()->createCourseSet(array('type' => 'normal', 'title' => 'course set4 title'));

        $dataTag = new FreeCourseSetsDataTag();

        $courseSets = $dataTag->getData(array('count' => '5'));
        $this->assertEquals(3, count($courseSets));

        $courseSets = $dataTag->getData(array('count' => '5', 'categoryId' => $category['id']));
        $this->assertEquals(1, count($courseSets));

        $courseSets = $dataTag->getData(array('count' => '5', 'categoryCode' => $category['code']));
        $this->assertEquals(1, count($courseSets));

        $courseSets = $dataTag->getData(array('count' => '5', 'orderby' => 'recommended'));
        $this->assertEquals($courseSet2['id'], $courseSets[0]['id']);
    }

    private function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    private function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }
}
