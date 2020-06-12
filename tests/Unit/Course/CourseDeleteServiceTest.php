<?php

namespace Tests\Unit\Course;

use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;

class CourseDeleteServiceTest extends BaseTestCase
{
    public function testDeleteCourseSet()
    {
        $courseSet = $this->getCourseSetDao()->create(['title' => 'course set name']);
        $this->assertNotNull($courseSet);

        $this->getCourseDeleteService()->deleteCourseSet($courseSet['id']);

        $result = $this->getCourseSetDao()->get($courseSet['id']);
        $this->assertNull($result);
    }

    public function testDeleteCourse()
    {
        $course = $this->getCourseDao()->create(['title' => 'course title', 'courseSetId' => 1]);
        $this->assertNotNull($course);

        $this->getCourseDeleteService()->deleteCourse($course['id']);

        $result = $this->getCourseDao()->get($course['id']);
        $this->assertNull($result);
    }

    public function testDeleteCourseSetMaterial()
    {
        $material = $this->getCourseMaterialDao()->create(['courseId' => 1, 'title' => 'material title', 'fileId' => 1, 'fileSize' => '1024', 'source' => 'coursematerial', 'type' => 'course', 'courseSetId' => 1]);
        $this->assertNotNull($material);

        ReflectionUtils::invokeMethod($this->getCourseDeleteService(), 'deleteCourseSetMaterial', [1]);

        $result = $this->getCourseMaterialDao()->get($material['id']);
        $this->assertNull($result);
    }

    public function testDeleteCourseSetCourse()
    {
        $result = ReflectionUtils::invokeMethod($this->getCourseDeleteService(), 'deleteCourseSetCourse', [1]);
        $this->assertEmpty($result);

        $course = $this->getCourseDao()->create(['title' => 'course title', 'courseSetId' => 1]);
        $this->assertNotNull($course);

        ReflectionUtils::invokeMethod($this->getCourseDeleteService(), 'deleteCourseSetCourse', [1]);

        $course = $this->getCourseDao()->get($course['id']);
        $this->assertNull($course);
    }

    public function testDeleteAttachment()
    {
        $result = ReflectionUtils::invokeMethod($this->getCourseDeleteService(), 'deleteAttachment', [1]);
        $this->assertTrue($result);

        $result = $this->getFileUsedDao()->create(['type' => 'attachment', 'targetId' => 100, 'targetType' => 'question.stem', 'fileId' => 1]);
        $this->assertNotNull($result);

        ReflectionUtils::invokeMethod($this->getCourseDeleteService(), 'deleteAttachment', [100]);

        $result = $this->getFileUsedDao()->get($result['id']);
        $this->assertNull($result);
    }

    public function testUpdateMobileSettingEmpty()
    {
        $result = ReflectionUtils::invokeMethod($this->getCourseDeleteService(), 'updateMobileSetting', [1]);
        $this->assertEmpty($result);

        $this->mockBiz('System:Setting', [
            [
                'functionName' => 'get',
                'returnValue' => ['courseIds' => [2, 3]],
            ],
        ]);

        $result = ReflectionUtils::invokeMethod($this->getCourseDeleteService(), 'updateMobileSetting', [1]);
        $this->assertEmpty($result);
    }

    public function testDeleteCourseMaterial()
    {
        $result = ReflectionUtils::invokeMethod($this->getCourseDeleteService(), 'deleteCourseMaterial', [1]);
        $this->assertEmpty($result);

        $material = $this->getCourseMaterialDao()->create(['courseId' => 1, 'title' => 'material title', 'fileId' => 1, 'fileSize' => '1024', 'source' => 'coursematerial', 'type' => 'course', 'courseSetId' => 1]);
        $this->assertNotNull($material);

        ReflectionUtils::invokeMethod($this->getCourseDeleteService(), 'deleteCourseMaterial', [1]);

        $result = $this->getCourseMaterialDao()->get($material['id']);
        $this->assertEmpty($result);
    }

    public function testDeleteCourseChapter()
    {
        $chapter = $this->getCourseChapterDao()->create(['courseId' => 1, 'type' => 'chapter', 'number' => 1, 'seq' => 1, 'title' => 'chapter one']);
        $this->assertNotNull($chapter);

        ReflectionUtils::invokeMethod($this->getCourseDeleteService(), 'deleteCourseChapter', [1]);

        $result = $this->getCourseChapterDao()->get($chapter['id']);
        $this->assertNull($result);
    }

    public function testDeleteTask()
    {
        $course = $this->getCourseDao()->create(['title' => 'course title', 'courseSetId' => 1]);
        $member = $this->getCourseMemberDao()->create(['courseId' => $course['id'], 'joinedType' => 'course', 'role' => 'student', 'userId' => $this->getCurrentUser()->getId(), 'courseSetId' => 1]);

        $activity = $this->getActivityDao()->create(['title' => 'activity title', 'mediaId' => 1, 'mediaType' => 'text', 'content' => 'activity content', 'fromCourseId' => $course['id'], 'fromCourseSetId' => $course['courseSetId'], 'fromUserId' => $this->getCurrentUser()->getId()]);
        $task = $this->getTaskDao()->create(['courseId' => $course['id'], 'fromCourseSetId' => 1, 'title' => 'task name', 'seq' => 1, 'activityId' => 1, 'type' => 'text', 'createdUserId' => 2]);

        $this->assertNotNull($task);

        ReflectionUtils::invokeMethod($this->getCourseDeleteService(), 'deleteTask', [1]);

        $result = $this->getTaskDao()->get($task['id']);
        $this->assertNull($result);
    }

    public function testDeleteTaskResult()
    {
        $result = $this->getTaskResultDao()->create(['activityId' => 1, 'courseId' => 1, 'courseTaskId' => 1, 'userId' => 1, 'status' => 'finish']);
        $this->assertNotNull($result);

        ReflectionUtils::invokeMethod($this->getCourseDeleteService(), 'deleteTaskResult', [1]);

        $result = $this->getTaskResultDao()->get($result['id']);
        $this->assertNull($result);
    }

    public function testDeleteJobEmpty()
    {
        $result = ReflectionUtils::invokeMethod($this->getCourseDeleteService(), 'deleteJob', [['type' => 'text']]);
        $this->assertEmpty($result);

        $result = ReflectionUtils::invokeMethod($this->getCourseDeleteService(), 'deleteJob', [['id' => 1, 'type' => 'live']]);
        $this->assertTrue(true);
    }

    public function testDeleteCourseNoteEmpty()
    {
        $result = ReflectionUtils::invokeMethod($this->getCourseDeleteService(), 'deleteCourseNote', [1]);
        $this->assertEmpty($result);
    }

    public function testDeleteCourseNote()
    {
        $result = ReflectionUtils::invokeMethod($this->getCourseDeleteService(), 'deleteCourseNote', [1]);
        $this->assertEmpty($result);

        $result = $this->getNoteDao()->create(['userId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'content' => 'note content', 'taskId' => 1]);
        $this->assertNotNull($result);

        ReflectionUtils::invokeMethod($this->getCourseDeleteService(), 'deleteCourseNote', [1]);

        $result = $this->getNoteDao()->get($result['id']);
        $this->assertNull($result);
    }

    public function testDeleteCourseAnnouncement()
    {
        $result = $this->getCourseDeleteService()->deleteCourseAnnouncement(1);
        $this->assertEmpty($result);

        $result = $this->getAnnouncementDao()->create(['userId' => 1, 'targetType' => 'course', 'targetId' => 100, 'content' => 'announcement content', 'url' => '']);
        $this->assertNotNull($result);

        $result = $this->getCourseDeleteService()->deleteCourseAnnouncement(100);

        $result = $this->getAnnouncementDao()->get($result['id']);
        $this->assertNull($result);
    }

    protected function getCourseSetDao()
    {
        return $this->createDao('Course:CourseSetDao');
    }

    protected function getCourseDao()
    {
        return $this->createDao('Course:CourseDao');
    }

    protected function getCourseMaterialDao()
    {
        return $this->createDao('Course:CourseMaterialDao');
    }

    protected function getQuestionDao()
    {
        return $this->createDao('Question:QuestionDao');
    }

    protected function getFileUsedDao()
    {
        return $this->createDao('File:FileUsedDao');
    }

    protected function getTestpaperDao()
    {
        return $this->createDao('Testpaper:TestpaperDao');
    }

    protected function getCourseChapterDao()
    {
        return $this->createDao('Course:CourseChapterDao');
    }

    protected function getTaskDao()
    {
        return $this->createDao('Task:TaskDao');
    }

    protected function getTaskResultDao()
    {
        return $this->createDao('Task:TaskResultDao');
    }

    protected function getCourseMemberDao()
    {
        return $this->createDao('Course:CourseMemberDao');
    }

    protected function getActivityDao()
    {
        return $this->createDao('Activity:ActivityDao');
    }

    protected function getNoteDao()
    {
        return $this->createDao('Course:CourseNoteDao');
    }

    protected function getAnnouncementDao()
    {
        return $this->createDao('Announcement:AnnouncementDao');
    }

    protected function getCourseDeleteService()
    {
        return $this->createService('Course:CourseDeleteService');
    }
}
