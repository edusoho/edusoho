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

    public function testOnUploadFileFinish()
    {
        $material = $this->getMaterialService()->addMaterial(array('courseId' => 1, 'courseSetId' => 2, 'title' => 1, 'fileId' => 2), array());
        $targetTypes = array('courseactivity', 'courselesson', 'coursematerial');
        $courseSetMaterialEventSubscriber = new CourseSetMaterialEventSubscriber($this->biz);

        foreach ($targetTypes as $targetTypes) {
            $this->mockBiz(
                'Course:MaterialService',
                array(
                    array(
                        'functionName' => 'uploadMaterial',
                        'returnValue' => array(),
                        'withParams' => array(
                            array(
                                'targetType' => $targetTypes,
                                'targetId' => 1,
                                'id' => 1,
                                'courseSetId' => 1,
                                'courseId' => 0,
                                'type' => 'course',
                                'fileId' => 1,
                                'source' => $targetTypes,
                            ),
                        ),
                    ),
                )
            );
            $event = new Event(array(
                'file' => array(
                    'targetType' => $targetTypes,
                    'targetId' => 1,
                    'id' => 1,
                ),
            ));

            $courseSetMaterialEventSubscriber->onUploadFileFinish($event);
        }

        $targetTypes = array('opencourselesson', 'opencoursematerial');
        foreach ($targetTypes as $targetTypes) {
            $this->mockBiz(
                'Course:MaterialService',
                array(
                    array(
                        'functionName' => 'uploadMaterial',
                        'returnValue' => array(),
                        'withParams' => array(
                            array(
                                'targetType' => $targetTypes,
                                'targetId' => 1,
                                'id' => 3,
                                'courseSetId' => 0,
                                'courseId' => 1,
                                'type' => 'openCourse',
                                'fileId' => 3,
                                'source' => $targetTypes,
                            ),
                        ),
                    ),
                )
            );
            $event = new Event(array(
                'file' => array(
                    'targetType' => $targetTypes,
                    'targetId' => 1,
                    'id' => 3,
                ),
            ));
            $courseSetMaterialEventSubscriber->onUploadFileFinish($event);
        }
    }

    public function testOnUploadFileDelete()
    {
        $courseSetMaterialEventSubscriber = new CourseSetMaterialEventSubscriber($this->biz);
        $event = new Event(array(
            'id' => 1,
        ));

        $result = $courseSetMaterialEventSubscriber->onUploadFileDelete($event);
        $this->assertTrue(!$result);

        $material = $this->getMaterialService()->addMaterial(array('courseId' => 1, 'courseSetId' => 2, 'title' => 1, 'fileId' => 1, 'lessonId' => 1), array());
        $courseSetMaterialEventSubscriber->onUploadFileDelete($event);
        $result = $this->getMaterialService()->searchMaterials(array(), array(), 0, \PHP_INT_MAX);
        $this->assertTrue(empty($result));
    }

    public function testOnOpenCourseDelete()
    {
        $courseSetMaterialEventSubscriber = new CourseSetMaterialEventSubscriber($this->biz);
        $event = new Event(array(
            'id' => 1,
        ));

        $material = $this->getMaterialService()->addMaterial(array('courseId' => 1, 'courseSetId' => 2, 'title' => 1, 'fileId' => 2, 'type' => 'openCourse'), array());
        $result = $this->getMaterialService()->searchMaterials(array(), array(), 0, \PHP_INT_MAX);
        $this->assertTrue(!empty($result));
        $courseSetMaterialEventSubscriber->onOpenCourseDelete($event);

        $result = $this->getMaterialService()->searchMaterials(array(), array(), 0, \PHP_INT_MAX);
        $this->assertTrue(empty($result));
    }

    public function testOnOpenCourseLessonCreate()
    {
        $courseSetMaterialEventSubscriber = new CourseSetMaterialEventSubscriber($this->biz);
        $event = new Event(array(
            'lesson' => array(
                'type' => 'liveOpen',
            ),
        ));

        $result = $courseSetMaterialEventSubscriber->onOpenCourseLessonCreate($event);
        $this->assertTrue(!$result);

        $event = new Event(array(
            'lesson' => array(
                'type' => 'test',
                'mediaId' => 0,
            ),
        ));

        $result = $courseSetMaterialEventSubscriber->onOpenCourseLessonCreate($event);
        $this->assertTrue(!$result);

        $event = new Event(array(
            'lesson' => array(
                'type' => 'course',
                'mediaId' => 1,
                'courseId' => 1,
                'id' => 1,
                'mediaId' => 1,
            ),
        ));

        $this->mockBiz(
            'Course:MaterialService',
            array(
                array(
                    'functionName' => 'searchMaterials',
                    'returnValue' => false,
                ),
                array(
                    'functionName' => 'uploadMaterial',
                    'returnValue' => array(),
                    'withParams' => array(
                        array(
                           'courseId' => 1, 'lessonId' => 1, 'fileId' => 1, 'source' => 'opencourselesson', 'type' => 'openCourse', 'courseSetId' => 0,
                        ),
                    ),
                ),
            )
        );
        $result = $courseSetMaterialEventSubscriber->onOpenCourseLessonCreate($event);
    }

    public function testOnOpenCourseLessonUpdate()
    {
        $courseSetMaterialEventSubscriber = new CourseSetMaterialEventSubscriber($this->biz);
        $event = new Event(array(
            'lesson' => array(
                'type' => 'liveOpen',
                'mediaId' => 1,
            ),
            'sourceLesson' => array(
                'type' => 'liveOpen',
                'mediaId' => 2,
            ),
        ));

        $result = $courseSetMaterialEventSubscriber->onOpenCourseLessonUpdate($event);
        $this->assertTrue(!$result);

        $event = new Event(array(
            'lesson' => array(
                'type' => 'course',
                'mediaId' => 1,
                'courseId' => 2,
                'id' => 5,
            ),
            'sourceLesson' => array(
                'type' => 'course',
                'mediaId' => 1,
                'courseId' => 2,
                'id' => 5,
            ),
        ));

        $result = $courseSetMaterialEventSubscriber->onOpenCourseLessonUpdate($event);
        $this->assertTrue(!$result);
    }

    public function testOnOpenCourseLessonUpdateWithExistMaterial()
    {
        $courseSetMaterialEventSubscriber = new CourseSetMaterialEventSubscriber($this->biz);
        $event = new Event(array(
            'lesson' => array(
                'type' => 'course',
                'mediaId' => 1,
                'courseId' => 2,
                'id' => 5,
            ),
            'sourceLesson' => array(
                'type' => 'course',
                'mediaId' => 2,
                'courseId' => 2,
                'id' => 5,
            ),
        ));

        $mockMaterialService = $this->mockBiz('Course:MaterialService', array(
            array('functionName' => 'searchMaterials', 'returnValue' => array()),
            array('functionName' => 'uploadMaterial', 'returnValue' => array()),
        ));
        $courseSetMaterialEventSubscriber->onOpenCourseLessonUpdate($event);

        $mockMaterialService->shouldHaveReceived('uploadMaterial');
    }

    public function testOnOpenCourseLessonUpdateWithMockMaterial()
    {
        $courseSetMaterialEventSubscriber = new CourseSetMaterialEventSubscriber($this->biz);
        $event = new Event(array(
            'lesson' => array(
                'type' => 'course',
                'mediaId' => 1,
                'courseId' => 2,
                'id' => 5,
                'mediaSource' => 'self',
            ),
            'sourceLesson' => array(
                'type' => 'course',
                'mediaId' => 2,
                'courseId' => 2,
                'id' => 5,
            ),
        ));

        $mockMaterialService = $this->mockBiz('Course:MaterialService', array(
            array('functionName' => 'searchMaterials', 'returnValue' => array(array('id' => 1, 'fileId' => 2))),
            array('functionName' => 'updateMaterial', 'returnValue' => array()),
            array('functionName' => 'uploadMaterial', 'returnValue' => array()),
        ));
        $courseSetMaterialEventSubscriber->onOpenCourseLessonUpdate($event);

        $mockMaterialService->shouldHaveReceived('uploadMaterial');
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function onOpenCourseLessonUpdateException(Event $event)
    {
        $courseSetMaterialEventSubscriber = new CourseSetMaterialEventSubscriber($this->biz);
        $event = new Event(array(
            'lesson' => array(
                'type' => 'course',
                'mediaId' => 1,
                'courseId' => 2,
                'id' => 5,
            ),
            'sourceLesson' => array(
                'type' => 'course',
                'mediaId' => 1,
                'courseId' => 2,
                'id' => 5,
            ),
        ));

        $material = $this->getMaterialService()->addMaterial(array('courseId' => 1, 'courseSetId' => 2, 'title' => 1, 'fileId' => 2, 'type' => 'openCourse'), array());
        $result = $courseSetMaterialEventSubscriber->onOpenCourseLessonUpdate($event);
    }

    public function testOnOpenCourseLessonDelete()
    {
        $courseSetMaterialEventSubscriber = new CourseSetMaterialEventSubscriber($this->biz);
        $event = new Event(array(
            'lesson' => array(
                'courseId' => 2,
                'id' => 5,
            ),
        ));
        $result = $courseSetMaterialEventSubscriber->onOpenCourseLessonDelete($event);
        $this->assertTrue(!$result);

        $material = $this->getMaterialService()->addMaterial(array('courseId' => 2, 'courseSetId' => 2, 'title' => 1, 'fileId' => 2, 'type' => 'openCourse', 'lessonId' => 5), array());
        $result = $courseSetMaterialEventSubscriber->onOpenCourseLessonDelete($event);
    }

    public function testOnLiveFileReplay()
    {
        $courseSetMaterialEventSubscriber = new CourseSetMaterialEventSubscriber($this->biz);
        $event = new Event(array(
            'lesson' => array(
                'courseId' => 2,
                'type' => 'course',
            ),
        ));
        $result = $courseSetMaterialEventSubscriber->onLiveFileReplay($event);

        $this->assertTrue(!$result);
        $event = new Event(array(
            'lesson' => array(
                'replayStatus' => '',
                'type' => 'live',
            ),
        ));
        $result = $courseSetMaterialEventSubscriber->onLiveFileReplay($event);
        $this->assertTrue(!$result);

        $event = new Event(array(
            'lesson' => array(
                'replayStatus' => 'videoGenerated',
                'type' => 'live',
                'courseId' => 10,
                'id' => 1,
                'mediaId' => 10,
            ),
        ));
        $mockMaterialService = $this->mockBiz(
            'Course:MaterialService',
            array(
                array(
                    'functionName' => 'searchMaterials',
                    'returnValue' => false,
                ),
                array(
                    'functionName' => 'uploadMaterial',
                    'returnValue' => array(),
                    'withParams' => array(
                        array(
                            'courseId' => 10,
                            'lessonId' => 1,
                            'fileId' => 10,
                            'source' => 'courselesson',
                            'type' => 'course',
                        ),
                    ),
                ),
            )
        );
        $courseSetMaterialEventSubscriber->onLiveFileReplay($event);

        $mockMaterialService->shouldHaveReceived('uploadMaterial');
    }

    public function testOnLiveOpenFileReplay()
    {
        $courseSetMaterialEventSubscriber = new CourseSetMaterialEventSubscriber($this->biz);
        $event = new Event(array(
            'lesson' => array(
                'courseId' => 2,
                'type' => 'course',
            ),
        ));
        $result = $courseSetMaterialEventSubscriber->onLiveOpenFileReplay($event);
        $this->assertTrue(!$result);

        $event = new Event(array(
            'lesson' => array(
                'replayStatus' => '',
                'type' => 'liveOpen',
            ),
        ));
        $result = $courseSetMaterialEventSubscriber->onLiveOpenFileReplay($event);
        $this->assertTrue(!$result);

        $event = new Event(array(
            'lesson' => array(
                'replayStatus' => 'videoGenerated',
                'type' => 'liveOpen',
                'courseId' => 10,
                'id' => 1,
                'mediaId' => 10,
            ),
        ));
        $this->mockBiz(
            'Course:MaterialService',
            array(
                array(
                    'functionName' => 'searchMaterials',
                    'returnValue' => false,
                ),
                array(
                    'functionName' => 'uploadMaterial',
                    'returnValue' => array(),
                    'withParams' => array(
                       array(
                            'courseId' => 10,
                            'lessonId' => 1,
                            'fileId' => 10,
                            'source' => 'opencourselesson',
                            'type' => 'openCourse',
                            'courseSetId' => 0,
                        ),
                    ),
                ),
            )
        );
        $result = $courseSetMaterialEventSubscriber->onLiveOpenFileReplay($event);
    }

    protected function getMaterialService()
    {
        return $this->createService('Course:MaterialService');
    }
}
