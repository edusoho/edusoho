<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\OpenCoursesDataTag;

class OpenCoursesDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCountEmpty()
    {
        $dataTag = new OpenCoursesDataTag();
        $dataTag->getData(array());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCountError()
    {
        $dataTag = new OpenCoursesDataTag();
        $dataTag->getData(array('countId' => 101));
    }

    public function testGetData()
    {
        $this->mockBiz('Taxonomy:CategoryService', array(
            array(
                'functionName' => 'findCategoriesByIds',
                'returnValue' => array('id' => 1, 'title' => 'open course lesson'),
            ),
            array(
                'functionName' => 'findCategoryChildrenIds',
                'returnValue' => array(1),
            ),
        ));

        $course1 = $this->getOpenCourseService()->createCourse(array('title' => 'course1 title', 'type' => 'open', 'about' => 'course1 about', 'categoryId' => 0));
        $this->getOpenCourseService()->updateCourse($course1['id'], array('status' => 'published'));
        $this->getOpenCourseService()->waveCourse($course1['id'], 'hitNum', +10);

        $course2 = $this->getOpenCourseService()->createCourse(array('title' => 'course2 title', 'type' => 'open', 'about' => 'course2 about', 'categoryId' => 1));
        $this->getOpenCourseService()->updateCourse($course2['id'], array('status' => 'published', 'recommended' => 1, 'recommendedSeq' => 1, 'recommendedTime' => time()));

        $course3 = $this->getOpenCourseService()->createCourse(array('title' => 'course3 title', 'type' => 'open', 'about' => 'course3 about', 'categoryId' => 1));
        $this->getOpenCourseService()->updateCourse($course3['id'], array('status' => 'published'));

        $course4 = $this->getOpenCourseService()->createCourse(array('title' => 'course4 title', 'type' => 'open', 'about' => 'course4 about', 'categoryId' => 0));
        $this->getOpenCourseService()->updateCourse($course4['id'], array('status' => 'published', 'recommended' => 1, 'recommendedSeq' => 2, 'recommendedTime' => time()));

        $course5 = $this->getOpenCourseService()->createCourse(array('title' => 'course5 title', 'type' => 'open', 'about' => 'course5 about', 'categoryId' => 0));
        $this->getOpenCourseService()->updateCourse($course5['id'], array('status' => 'published'));

        $course6 = $this->getOpenCourseService()->createCourse(array('title' => 'course6 title', 'type' => 'open', 'about' => 'course6 about', 'categoryId' => 0));

        $dataTag = new OpenCoursesDataTag();
        $datas = $dataTag->getData(array('count' => 5));
        $this->assertEquals(5, count($datas));

        $dataTag = new OpenCoursesDataTag();
        $datas = $dataTag->getData(array('count' => 5, 'orderBy' => 'hitNum'));
        $this->assertEquals(5, count($datas));
        $this->assertEquals($course1['id'], $datas[0]['id']);

        $dataTag = new OpenCoursesDataTag();
        $datas = $dataTag->getData(array('count' => 5, 'orderBy' => 'recommendedSeq'));
        $this->assertEquals(5, count($datas));
        $this->assertEquals($course2['id'], $datas[0]['id']);
        $this->assertEquals(0, $datas[4]['recommended']);

        $dataTag = new OpenCoursesDataTag();
        $datas = $dataTag->getData(array('count' => 5, 'categoryId' => 1));
        $this->assertEquals(2, count($datas));
    }

    protected function getOpenCourseService()
    {
        return $this->createService('OpenCourse:OpenCourseService');
    }
}
