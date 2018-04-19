<?php

namespace Tests\Unit\Course\Event;

use Codeages\Biz\Framework\Event\Event;
use Biz\Course\Event\CourseSetMaterialEventSubscriber;
use Biz\BaseTestCase;

class CourseSetMaterialEventSubscriberTest extends BaseTestCase
{
    public function testGetSubscribedEvents()
    {
        $courseSetMaterialEventSubscriber = new CourseSetMaterialEventSubscriber($this->biz);
        $result = $courseSetMaterialEventSubscriber::getSubscribedEvents();

        $this->assertArrayEquals(array(
            'course-set.delete' => 'onCourseSetDelete',
            'course.delete' => 'onCourseDelete',
            'course.activity.create' => 'onCourseActivityCreate',
            'course.activity.delete' => 'onCourseActivityDelete',
            'course.activity.update' => 'onCourseActivityUpdate',
            'upload.file.delete' => 'onUploadFileDelete',
            'upload.file.finish' => 'onUploadFileFinish',
            'upload.file.add' => 'onUploadFileFinish',
            'open.course.delete' => 'onOpenCourseDelete',
            'open.course.lesson.create' => 'onOpenCourseLessonCreate',
            'open.course.lesson.update' => 'onOpenCourseLessonUpdate',
            'open.course.lesson.delete' => 'onOpenCourseLessonDelete',
            'open.course.lesson.generate.video.replay' => 'onLiveOpenFileReplay',
        ), $result);
    }

    public function testOnCourseSetDelete()
    {
        $this->getMaterialService()->addMaterial(array('courseId' => 1, 'courseSetId' => 2, 'title' => 1, 'fileId' => 2), array());
        $event = new Event(array(
            'id' => 2,
        ));

        $result = $this->getMaterialService()->searchMaterials(array(), array(), 0, 1);
        $this->assertTrue(!empty($result));
        $courseSetMaterialEventSubscriber = new CourseSetMaterialEventSubscriber($this->biz);
        $courseSetMaterialEventSubscriber->onCourseSetDelete($event);
        
        $result = $this->getMaterialService()->searchMaterials(array(), array(), 0, 1);
        $this->assertTrue(empty($result));
    }

    public function testOnCourseDelete()
    {
        $this->getMaterialService()->addMaterial(array('courseId' => 1, 'courseSetId' => 2, 'title' => 1, 'fileId' => 2), array());
        $event = new Event(array(
            'id' => 1,
        ));

        $result = $this->getMaterialService()->searchMaterials(array(), array(), 0, 1);
        $this->assertTrue(!empty($result));
        $courseSetMaterialEventSubscriber = new CourseSetMaterialEventSubscriber($this->biz);
        $courseSetMaterialEventSubscriber->onCourseDelete($event);
        
        $result = $this->getMaterialService()->searchMaterials(array(), array(), 0, 1);
        $this->assertTrue(empty($result));
    }   

    public function testOnCourseActivityCreate()
    {
        $types = array('testpaper', 'live', 'text');

        foreach($types as $type) {
            $event = new Event(array(
                'argument' => array(),
                'activity' => array('type' => $type),
                'mediaId' => 1,
            ));
            $courseSetMaterialEventSubscriber = new CourseSetMaterialEventSubscriber($this->biz);
            $result = $courseSetMaterialEventSubscriber->onCourseActivityCreate($event);
            $this->assertTrue(!$result);
        }
    
        $this->getMaterialService()->addMaterial(array('courseId' => 1, 'courseSetId' => 3, 'title' => 1, 'fileId' => 1, 'lessonId' => 1, 'source' => 'courseactivity'), array());
        $event = new Event(array(
            'argument' => array(),
            'activity' => array('type' => 'video', 'mediaId' => 1, 'courseId' => 1, 'id' => 1, 'fileId' => 2, 'courseSetId' => 3),
        ));
        $courseSetMaterialEventSubscriber = new CourseSetMaterialEventSubscriber($this->biz);
        $result = $courseSetMaterialEventSubscriber->onCourseActivityCreate($event);
    }

    public function testOnCourseActivityDelete()
    {
        
    }

    protected function getMaterialService()
    {
        return $this->createService('Course:MaterialService');
    }
}
