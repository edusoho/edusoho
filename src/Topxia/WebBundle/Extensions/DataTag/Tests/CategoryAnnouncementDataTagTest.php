<?php

namespace Topxia\WebBundle\Extensions\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\WebBundle\Extensions\DataTag\CategoryAnnouncementDataTag;

class CategoryAnnouncementDataTagTest extends BaseTestCase
{

   /* public function testGetData()
    {
       //添加category
        $groupId = $this->getCategoryService()->addGroup(array(
        "code" => "course",
        "name" => "课程分类",
        "depth" => "2"
        ));
       
        $CategoryId1 = $this->getCategoryService()->createCategory(array(
        "code" => "default",
        "name" => "默认分类",
        "icon" => "",
        "path" => "",
        "weight" => 100,
        "groupId" => $groupId['id'],
        "parentId" => 0,
        "description" => ""
        ));

        $CategoryId2 = $this->getCategoryService()->createCategory(array(
        "code" => "php",
        "name" => "php",
        "icon" => "",
        "path" => "",
        "weight" => 0,
        "groupId" => $groupId['id'],
        "parentId" => 0,
        "description" => ""
        ));

        //添加课程
        $course1 = $this->getCourseService()->createCourse(array(
        "title" => "title 1",
        "status" => "published",
        "categoryId" => $CategoryId1["id"]
        ));
        $this->getCourseService()->publishCourse($course1['id']);

        $course2 = $this->getCourseService()->createCourse(array(
        "title" => "title 2",
        "status" => "published",
        "categoryId" => $CategoryId1["id"]
        ));
        $this->getCourseService()->publishCourse($course2['id']);

        $course3 = $this->getCourseService()->createCourse(array(
        "title" => "title 3",
        "status" => "published",
        "categoryId" => $CategoryId2["id"]
        ));
        $this->getCourseService()->publishCourse($course3['id']);

        $course4 = $this->getCourseService()->createCourse(array(
        "title" => "title 4",
        "status" => "published",
        ));
        $this->getCourseService()->publishCourse($course4['id']);

        $course5 = $this->getCourseService()->createCourse(array(
        "title" => "title 5",
        "status" => "published",
        ));
        $this->getCourseService()->publishCourse($course5['id']);

        //添加课程公告列表
        $courseAnnouncement1 = $this->getCourseService()->createAnnouncement($course1['id'], array(
         "userId" => "1",
         "content" =>"courseAnnouncement 1"
        ));

        $courseAnnouncement2 = $this->getCourseService()->createAnnouncement($course2['id'], array(
         "userId" => "1",
         "content" =>"courseAnnouncement 2"
        ));

        $courseAnnouncement3 = $this->getCourseService()->createAnnouncement($course1['id'], array(
         "userId" => "1",
         "content" =>"courseAnnouncement 3"
        ));

        $courseAnnouncement4 = $this->getCourseService()->createAnnouncement($course1['id'], array(
         "userId" => "1",
         "content" =>"courseAnnouncement 4"
        ));

        $courseAnnouncement5 = $this->getCourseService()->createAnnouncement($course5['id'], array(
         "userId" => "1",
         "content" =>"courseAnnouncement 5"
        ));

        $datatag = new CategoryAnnouncementDataTag();
        $categoryAnnouncement = $datatag->getData(array('count' => "5"));
        $this->assertEquals(5, count($categoryAnnouncement));
        $categoryAnnouncement = $datatag->getData(array('categoryId'=>$CategoryId1['id'],'count' => "5"));
    	$this->assertEquals(4, count($categoryAnnouncement));
    }

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }*/
}
