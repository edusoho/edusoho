<?php

namespace Tests\Unit\File\Event;

use Biz\BaseTestCase;
use Biz\Course\Service\LiveReplayService;
use Biz\File\Dao\FileUsedDao;
use Biz\File\Dao\UploadFileDao;
use Biz\File\Event\UploadFileEventSubscriber;
use Biz\File\Service\UploadFileService;
use Biz\OpenCourse\Service\OpenCourseService;
use Biz\Question\Service\QuestionService;
use Codeages\Biz\Framework\Event\Event;

class UploadFileEventSubscriberTest extends BaseTestCase
{
    public function testGetSubscribedEvents()
    {
        $expected = array(
            'question.create' => array('onQuestionCreate', 2),
            'question.update' => array('onQuestionUpdate', 2),
            'question.delete' => array('onQuestionDelete', 2),

            'course.delete' => 'onCourseDelete',

            'course.material.create' => 'onMaterialCreate',
            'course.material.update' => 'onMaterialUpdate',
            'course.material.delete' => 'onMaterialDelete',

            'open.course.lesson.delete' => 'onOpenCourseLessonDelete',
            'open.course.delete' => 'onOpenCourseDelete',

            'article.delete' => 'onArticleDelete',
            'group.thread.post.delete' => 'onGroupThreadPostDelete',
            'group.thread.delete' => 'onGroupThreadDelete',
            'course.thread.delete' => 'onCourseThreadDelete',
            'course.thread.post.delete' => 'onCourseThreadPostDelete',
            'thread.delete' => 'onThreadDelete',
            'thread.post.delete' => 'onThreadPostDelete',

            'delete.use.file' => 'onDeleteUseFiles',

            'live.activity.update' => 'onLiveActivityUpdate',
        );

        $eventSubscriber = new UploadFileEventSubscriber($this->biz);
        $result = $eventSubscriber::getSubscribedEvents();

        $this->assertEquals($expected, $result);
    }

    public function testOnQuestionCreateNull()
    {
        $eventSubscriber = new UploadFileEventSubscriber($this->biz);
        $event = new Event(array('id' => 1));
        $result = $eventSubscriber->onQuestionCreate($event);
        $this->assertNull($result);

        $event->setArgument('argument', array());
        $result = $eventSubscriber->onQuestionCreate($event);
        $this->assertNull($result);

        $argument = array(
            'attachment' => array(
                'stem' => array(
                    'fileIds' => array(1),
                    'targetType' => 'question',
                    'type' => 'course',
                ),
                'analysis' => array(
                    'fileIds' => array(2),
                    'targetType' => 'question',
                    'type' => 'question',
                ),
            ),
        );
        $event->setArgument('argument', $argument);
        $eventSubscriber->onQuestionCreate($event);

        $fileUsed1 = $this->getFileUsedDao()->get(1);
        $fileUsed2 = $this->getFileUsedDao()->get(2);

        $this->assertNotEmpty($fileUsed1);
        $this->assertNotEmpty($fileUsed2);
    }

    public function testOnQuestionUpdate()
    {
        $eventSubscriber = new UploadFileEventSubscriber($this->biz);
        $event = new Event(array('id' => 1));
        $result = $eventSubscriber->onQuestionUpdate($event);
        $this->assertNull($result);

        $event->setArgument('argument', array());
        $result = $eventSubscriber->onQuestionUpdate($event);
        $this->assertNull($result);

        $argument = array(
            'fields' => array(
                'attachment' => array(
                    'stem' => array(
                        'fileIds' => array(1),
                        'targetType' => 'question',
                        'type' => 'course',
                    ),
                    'analysis' => array(
                        'fileIds' => array(2),
                        'targetType' => 'question',
                        'type' => 'question',
                    ),
                ),
            ),
        );
        $event->setArgument('argument', $argument);
        $eventSubscriber->onQuestionUpdate($event);

        $fileUsed1 = $this->getFileUsedDao()->get(1);
        $fileUsed2 = $this->getFileUsedDao()->get(2);

        $this->assertNotEmpty($fileUsed1);
        $this->assertNotEmpty($fileUsed2);
    }

    public function testOnQuestionDelete()
    {
        $file1 = $this->createUsedFile(1, 'question_test', 1, 'course_test');
        $eventSubscriber = new UploadFileEventSubscriber($this->biz);
        $event = new Event(array('id' => 1));
        $eventSubscriber->onQuestionDelete($event);

        $usedFile = $this->getFileUsedDao()->get($file1['id']);
        $this->assertNotEmpty($usedFile);

        $file2 = $this->createUsedFile(2, 'question.stem', 1, 'attachment');
        $eventSubscriber->onQuestionDelete($event);

        $usedFile = $this->getFileUsedDao()->get($file2['id']);
        $this->assertEmpty($usedFile);
    }

    public function testOnArticleDelete()
    {
        $event = new Event(array('id' => 1));
        $eventSubscriber = new UploadFileEventSubscriber($this->biz);

        $file = $this->createUsedFile(2, 'article', 1, 'attachment');
        $eventSubscriber->onArticleDelete($event);

        $usedFile = $this->getFileUsedDao()->get($file['id']);
        $this->assertEmpty($usedFile);
    }

    public function testOnGroupThreadPostDelete()
    {
        $event = new Event(array('id' => 1));
        $eventSubscriber = new UploadFileEventSubscriber($this->biz);

        $file = $this->createUsedFile(2, 'group.thread.post', 1, 'attachment');
        $eventSubscriber->onGroupThreadPostDelete($event);

        $usedFile = $this->getFileUsedDao()->get($file['id']);
        $this->assertEmpty($usedFile);
    }

    public function testOnGroupThreadDelete()
    {
        $event = new Event(array('id' => 1));
        $eventSubscriber = new UploadFileEventSubscriber($this->biz);

        $file = $this->createUsedFile(2, 'group.thread', 1, 'attachment');
        $eventSubscriber->onGroupThreadDelete($event);

        $usedFile = $this->getFileUsedDao()->get($file['id']);
        $this->assertEmpty($usedFile);
    }

    public function testOnCourseThreadDelete()
    {
        $event = new Event(array('id' => 1));
        $eventSubscriber = new UploadFileEventSubscriber($this->biz);

        $file = $this->createUsedFile(2, 'course.thread', 1, 'attachment');
        $eventSubscriber->onCourseThreadDelete($event);

        $usedFile = $this->getFileUsedDao()->get($file['id']);
        $this->assertEmpty($usedFile);
    }

    public function testOnCourseThreadPostDelete()
    {
        $event = new Event(array('id' => 1));
        $eventSubscriber = new UploadFileEventSubscriber($this->biz);

        $file = $this->createUsedFile(2, 'course.thread.post', 1, 'attachment');
        $eventSubscriber->onCourseThreadPostDelete($event);

        $usedFile = $this->getFileUsedDao()->get($file['id']);
        $this->assertEmpty($usedFile);
    }

    public function testOnThreadDelete()
    {
        $event = new Event(array(
            'targetType' => 'test',
            'id' => 1,
        ));
        $eventSubscriber = new UploadFileEventSubscriber($this->biz);

        $file = $this->createUsedFile(2, 'test.thread', 1, 'attachment');
        $eventSubscriber->onThreadDelete($event);

        $usedFile = $this->getFileUsedDao()->get($file['id']);
        $this->assertEmpty($usedFile);
    }

    public function testOnThreadPostDelete()
    {
        $event = new Event(array(
            'targetType' => 'test',
            'id' => 1,
        ));
        $eventSubscriber = new UploadFileEventSubscriber($this->biz);

        $file = $this->createUsedFile(2, 'test.thread.post', 1, 'attachment');
        $eventSubscriber->onThreadPostDelete($event);

        $usedFile = $this->getFileUsedDao()->get($file['id']);
        $this->assertEmpty($usedFile);
    }

    public function testOnCourseLessonCreate()
    {
        $file = $this->createUploadFile(1);
        $event = new Event(array(
            'lesson' => array(
                'mediaId' => $file['id'],
                'type' => 'audio',
            ),
        ));

        $eventSubscriber = new UploadFileEventSubscriber($this->biz);
        $eventSubscriber->onCourseLessonCreate($event);

        $result = $this->getUploadFileDao()->get($file['id']);
        $this->assertEquals(1, $file['usedCount']);
        $this->assertEquals(2, $result['usedCount']);
    }

    public function testOnCourseLessonDelete()
    {
        $file = $this->createUploadFile(1);
        $event = new Event(array(
            'mediaId' => $file['id'],
        ));

        $eventSubscriber = new UploadFileEventSubscriber($this->biz);
        $eventSubscriber->onCourseLessonDelete($event);

        $result = $this->getUploadFileDao()->get($file['id']);
        $this->assertEquals(1, $file['usedCount']);
        $this->assertEquals(0, $result['usedCount']);
    }

    public function testOnMaterialCreate()
    {
        $file = $this->createUploadFile(1);
        $event = new Event(array(
            'fileId' => $file['id'],
        ));

        $eventSubscriber = new UploadFileEventSubscriber($this->biz);
        $eventSubscriber->onMaterialCreate($event);

        $result = $this->getUploadFileDao()->get($file['id']);

        $this->assertEquals(1, $file['usedCount']);
        $this->assertEquals(2, $result['usedCount']);
    }

    public function testOnMaterialDeleteWithFileNull()
    {
        $event = new Event(array(
            'fileId' => 1,
        ));

        $eventSubscriber = new UploadFileEventSubscriber($this->biz);
        $result = $eventSubscriber->onMaterialDelete($event);
        $this->assertNull($result);
    }

    public function testOnMaterialDeleteWithoutManageRole()
    {
        $this->mockBiz('File:UploadFileService', array(
            array(
                'functionName' => 'canManageFile',
                'returnValue' => false,
                'withParams' => array(1),
            ),
            array(
                'functionName' => 'getFile',
                'returnValue' => array('id' => 1),
                'withParams' => array(1),
            ),
            array(
                'functionName' => 'waveUsedCount',
                'returnValue' => true,
                'withParams' => array(1, -1),
            ),
        ));

        $event = new Event(array(
            'fileId' => 1,
        ));
        $eventSubscriber = new UploadFileEventSubscriber($this->getServiceKernel()->getBiz());
        $result = $eventSubscriber->onMaterialDelete($event);
        $this->assertNull($result);
    }

    public function testOnMaterialDelete()
    {
        $file = $this->createUploadFile(1);
        $event = new Event(array(
            'fileId' => $file['id'],
            'courseId' => $file['targetId'],
        ));

        $eventSubscriber = new UploadFileEventSubscriber($this->biz);
        $eventSubscriber->onMaterialDelete($event);

        $result = $this->getUploadFileDao()->get($file['id']);

        $this->assertEquals(2, $file['targetId']);
        $this->assertEquals(0, $result['targetId']);
    }

    public function testOnMaterialUpdateWithSourceMaterialLessonId()
    {
        $file = $this->createUploadFile(1);
        $event = new Event(array(
            'lessonId' => 0,
            'fileId' => $file['id'],
        ));
        $event->setArgument('argument', array());
        $event->setArgument('sourceMaterial', array('lessonId' => 1));

        $eventSubscriber = new UploadFileEventSubscriber($this->biz);
        $eventSubscriber->onMaterialUpdate($event);

        $result = $this->getUploadFileDao()->get($file['id']);
        $this->assertEquals(1, $file['usedCount']);
        $this->assertEquals(0, $result['usedCount']);
    }

    public function testOnMaterialWithArgumentFileId()
    {
        $file1 = $this->createUploadFile(1);
        $file2 = $this->createUploadFile(2);
        $event = new Event(array(
            'lessonId' => 1,
            'fileId' => $file1['id'],
        ));
        $event->setArgument('argument', array('fileId' => $file2['id']));
        $event->setArgument('sourceMaterial', array('lessonId' => 0));

        $eventSubscriber = new UploadFileEventSubscriber($this->biz);
        $eventSubscriber->onMaterialUpdate($event);

        $result1 = $this->getUploadFileDao()->get($file1['id']);
        $result2 = $this->getUploadFileDao()->get($file2['id']);
        $this->assertEquals(1, $file1['usedCount']);
        $this->assertEquals(2, $result1['usedCount']);

        $this->assertEquals(1, $file2['usedCount']);
        $this->assertEquals(0, $result2['usedCount']);
    }

    public function testOnMaterialWithMaterialLessonId()
    {
        $file = $this->createUploadFile(1);
        $event = new Event(array(
            'lessonId' => 1,
            'fileId' => $file['id'],
        ));
        $event->setArgument('argument', array('fileId' => $file['id']));
        $event->setArgument('sourceMaterial', array('lessonId' => 0));

        $eventSubscriber = new UploadFileEventSubscriber($this->biz);
        $eventSubscriber->onMaterialUpdate($event);

        $result = $this->getUploadFileDao()->get($file['id']);
        $this->assertEquals(1, $file['usedCount']);
        $this->assertEquals(2, $result['usedCount']);
    }

    public function testOnOpenCourseLessonDelete()
    {
        $file = $this->createUploadFile(1);
        $event = new Event(array(
            'lesson' => array(
                'mediaId' => $file['id'],
                'type' => 'audio',
            ),
        ));

        $eventSubscriber = new UploadFileEventSubscriber($this->biz);
        $eventSubscriber->onOpenCourseLessonDelete($event);

        $result = $this->getUploadFileDao()->get($file['id']);

        $this->assertEquals(1, $file['usedCount']);
        $this->assertEquals(0, $result['usedCount']);
    }

    public function testOnOpenCourseDelete()
    {
        $file = $this->createUploadFile(1);

        $course = $this->getOpenCourseService()->createCourse(
            array(
                'title' => 'open course title',
                'type' => 'open',
                'about' => 'open course about',
                'categoryId' => 0,
            )
        );

        $this->getOpenCourseService()->createLesson(
            array(
                'courseId' => $course['id'],
                'title' => $course['title'].'课时1',
                'type' => 'video',
                'mediaId' => $file['id'],
                'mediaName' => '',
                'mediaUri' => '',
                'mediaSource' => 'self',
            )
        );
        $this->getOpenCourseService()->updateCourse($course['id'], array('status' => 'published'));

        $event = new Event(array(
            'id' => $course['id'],
        ));

        $eventSubscriber = new UploadFileEventSubscriber($this->biz);
        $eventSubscriber->onOpenCourseDelete($event);

        $result = $this->getUploadFileDao()->get($file['id']);

        $this->assertEquals(1, $file['usedCount']);
        $this->assertEquals(2, $result['usedCount']);
    }

    public function testOnDeleteUseFilesWithWrongParams()
    {
        $event = new Event(array(
            'type' => 'test.type',
        ));
        $eventSubscriber = new UploadFileEventSubscriber($this->biz);
        $result = $eventSubscriber->onDeleteUseFiles($event);

        $this->assertNull($result);

        $event = new Event(
            array(
                'type' => 'attachment',
                'targetType' => 'testTargetType',
            )
        );
        $eventSubscriber = new UploadFileEventSubscriber($this->biz);
        $result = $eventSubscriber->onDeleteUseFiles($event);

        $this->assertNull($result);
    }

    public function testOnDeleteUseFilesWithQuestionCopyId()
    {
        $event = new Event(
            array(
                'type' => 'attachment',
                'targetType' => 'question.stem',
                'targetId' => 1,
            )
        );
        $this->mockBiz('Question:QuestionService', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('copyId' => 1),
                'withParams' => array(1),
            ),
        ));
        $eventSubscriber = new UploadFileEventSubscriber($this->biz);
        $result = $eventSubscriber->onDeleteUseFiles($event);

        $this->assertNull($result);
    }

    public function testOnDeleteUseFilesWithQuestionsEmpty()
    {
        $event = new Event(
            array(
                'type' => 'attachment',
                'targetType' => 'question.stem',
                'targetId' => 1,
            )
        );
        $this->mockBiz('Question:QuestionService', array(
            array(
                'functionName' => 'get',
                'returnValue' => array(
                    'copyId' => 0,
                    'id' => 1,
                ),
                'withParams' => array(1),
            ),
            array(
                'functionName' => 'findQuestionsByCopyId',
                'returnValue' => array(),
                'withParams' => array(1),
            ),
        ));
        $eventSubscriber = new UploadFileEventSubscriber($this->biz);
        $result = $eventSubscriber->onDeleteUseFiles($event);

        $this->assertNull($result);
    }

    public function testOnDeleteUseFilesWithAttachmentsEmpty()
    {
        $event = new Event(
            array(
                'type' => 'attachment',
                'targetType' => 'question.stem',
                'targetId' => 1,
            )
        );
        $this->mockBiz('Question:QuestionService', array(
            array(
                'functionName' => 'get',
                'returnValue' => array(
                    'copyId' => 0,
                    'id' => 1,
                ),
                'withParams' => array(1),
            ),
            array(
                'functionName' => 'findQuestionsByCopyId',
                'returnValue' => array(
                    array('id' => 1),
                ),
                'withParams' => array(1),
            ),
        ));
        $eventSubscriber = new UploadFileEventSubscriber($this->biz);
        $result = $eventSubscriber->onDeleteUseFiles($event);

        $this->assertNull($result);
    }

    public function testOnDeleteUseFiles()
    {
        $question = array(
            'id' => 1,
            'type' => 'single_choice',
            'parentId' => 0,
            'stem' => '111',
            'answer' => array(1),
            'choices' => array(1, 2, 3, 4),
            'target' => 'course-1',
            'courseSetId' => 1,
        );
        $question = $this->getQuestionService()->create($question);
        $copyQuestion = array(
            'id' => 2,
            'type' => 'single_choice',
            'parentId' => 0,
            'stem' => '111',
            'answer' => array(1),
            'choices' => array(1, 2, 3, 4),
            'target' => 'course-1',
            'courseSetId' => 1,
            'copyId' => $question['id'],
        );
        $copyQuestion = $this->getQuestionService()->create($copyQuestion);

        $file = $this->createUsedFile(1, 'question.stem', $copyQuestion['id'], 'attachment');
        $event = new Event(
            array(
                'type' => 'attachment',
                'targetType' => 'question.stem',
                'targetId' => $question['id'],
            )
        );

        $eventSubscriber = new UploadFileEventSubscriber($this->biz);
        $eventSubscriber->onDeleteUseFiles($event);
        $result = $this->getFileUsedDao()->get($file['id']);

        $this->assertEmpty($result);
    }

    public function testOnLiceActivityUpdate()
    {
        $file = $this->createUploadFile(1);

        $event = new Event();
        $event->setArgument('fields', array(
            'mediaId' => $file['id'],
            'replayStatus' => LiveReplayService::REPLAY_VIDEO_GENERATE_STATUS,
        ));

        $eventSubscriber = new UploadFileEventSubscriber($this->biz);
        $eventSubscriber->onLiveActivityUpdate($event);

        $result = $this->getUploadFileDao()->get($file['id']);
        $this->assertEquals(1, $file['usedCount']);
        $this->assertEquals(2, $result['usedCount']);
    }

    /**
     * @return UploadFileService
     */
    private function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File:UploadFileService');
    }

    private function createUploadFile($id)
    {
        return $this->getUploadFileDao()->create(array(
            'id' => $id,
            'globalId' => 0,
            'status' => 'ok',
            'hashId' => 'course-activity/2/'.rand(0, 100000).'-fd0zox.mp3',
            'targetId' => 2,
            'targetType' => 'course-activity',
            'filename' => 'test.mp3',
            'ext' => 'mp3',
            'convertHash' => 'ch-course-activity/2/'.rand(0, 100000).'-fd0zox.mp3',
            'storage' => 'local',
            'convertStatus' => 'none',
            'isPublic' => 0,
            'canDownload' => 0,
            'usedCount' => 1,
            'updatedUserId' => 1,
            'createdUserId' => 2,
            'audioConvertStatus' => 'none',
            'mp4ConvertStatus' => 'none',
            'length' => 12,
            'type' => 'audio',
            'fileSize' => 12,
            'createdTime' => time(),
            'updatedTime' => time(),
        ));
    }

    private function createUsedFile($fileId, $targetType, $targetId, $type)
    {
        return $this->getFileUsedDao()->create(
            array(
                'fileId' => $fileId,
                'targetType' => $targetType,
                'targetId' => $targetId,
                'type' => $type,
                'createdTime' => time(),
            )
        );
    }

    /**
     * @return UploadFileDao
     */
    private function getUploadFileDao()
    {
        return $this->createDao('File:UploadFileDao');
    }

    /**
     * @return FileUsedDao
     */
    private function getFileUsedDao()
    {
        return $this->createDao('File:FileUsedDao');
    }

    /**
     * @return OpenCourseService
     */
    private function getOpenCourseService()
    {
        return $this->createService('OpenCourse:OpenCourseService');
    }

    /**
     * @return QuestionService
     */
    private function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }
}
