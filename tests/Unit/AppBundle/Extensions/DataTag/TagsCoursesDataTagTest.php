<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\TagsCoursesDataTag;

class TagsCoursesDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $tag1 = $this->getTagService()->addTag(array('name' => 'tag1'));
        $tag2 = $this->getTagService()->addTag(array('name' => 'tag2'));
        $course1 = array(
            'type' => 'normal',
            'title' => 'course1',
        );
        $course2 = array(
            'type' => 'normal',
            'title' => 'course2',
        );
        $course3 = array(
            'type' => 'normal',
            'title' => 'course3',
        );

        $course1 = $this->getCourseService()->createCourse($course1);
        $course2 = $this->getCourseService()->createCourse($course2);
        $course3 = $this->getCourseService()->createCourse($course3);
        $this->getCourseService()->publishCourse($course1['id']);
        $this->getCourseService()->publishCourse($course2['id']);
        $this->getCourseService()->publishCourse($course3['id']);
        $this->getCourseService()->updateCourse($course1['id'], array('tags' => 'tag1,tag2'));
        $this->getCourseService()->updateCourse($course2['id'], array('tags' => 'tag2'));
        $this->getCourseService()->updateCourse($course3['id'], array('tags' => 'tag1'));
        $datatag = new TagsCoursesDataTag();
        $courses = $datatag->getData(array('count' => 5, 'tags' => array('tag1', 'tag2')));
        $this->assertEquals(1, count($courses));
        $courses = $datatag->getData(array('count' => 5, 'tags' => array('tag2')));
        $this->assertEquals(2, count($courses));
    }

    public function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy:TagService');
    }

    public function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }
}
