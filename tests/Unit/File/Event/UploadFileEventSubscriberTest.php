<?php

namespace Tests\Unit\File\Event;

use Biz\BaseTestCase;
use Biz\File\Dao\FileUsedDao;
use Biz\File\Event\UploadFileEventSubscriber;
use Biz\File\Service\UploadFileService;
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
                    'type' => 'course'
                ),
                'analysis' => array(
                    'fileIds' => array(2),
                    'targetType' => 'question',
                    'type' => 'question'
                )
            )
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
                        'type' => 'course'
                    ),
                    'analysis' => array(
                        'fileIds' => array(2),
                        'targetType' => 'question',
                        'type' => 'question'
                    )
                )
            )
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
            'id' => 1
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
            'id' => 1
        ));
        $eventSubscriber = new UploadFileEventSubscriber($this->biz);

        $file = $this->createUsedFile(2, 'test.thread.post', 1, 'attachment');
        $eventSubscriber->onThreadPostDelete($event);

        $usedFile = $this->getFileUsedDao()->get($file['id']);
        $this->assertEmpty($usedFile);
    }

    public function testOnCourseLessonCreate()
    {
        $event = new Event(array(
            'lesson' => array(
                'mediaId' => 1,
                'type' => 'audio'
            )
        ));

        $eventSubscriber = new UploadFileEventSubscriber($this->biz);
        $eventSubscriber->onCourseLessonCreate($event);
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

    private function mockUploadFile()
    {
        $this->mockBiz('File:UploadFileService', array(
            'functionName' => 'waveUsedCount',
            'returnValue' => array('id' => 1),
        ));
    }

    /**
     * @return FileUsedDao
     */
    private function getFileUsedDao()
    {
        return $this->createDao('File:FileUsedDao');
    }

    /**
     * @return UploadFileService
     */
    private function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }
}