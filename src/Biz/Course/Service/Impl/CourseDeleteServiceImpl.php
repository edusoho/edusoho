<?php

namespace Biz\Course\Service\Impl;

use Biz\Activity\Service\ActivityService;
use Biz\Announcement\Service\AnnouncementService;
use Biz\BaseService;
use Biz\Course\Dao\CourseChapterDao;
use Biz\Course\Dao\CourseDao;
use Biz\Course\Dao\CourseMemberDao;
use Biz\Course\Dao\CourseNoteDao;
use Biz\Course\Dao\CourseNoteLikeDao;
use Biz\Course\Dao\CourseSetDao;
use Biz\Course\Dao\ThreadDao;
use Biz\Course\Dao\ThreadPostDao;
use Biz\Course\Service\CourseDeleteService;
use Biz\Favorite\Dao\FavoriteDao;
use Biz\IM\Service\ConversationService;
use Biz\Review\Dao\ReviewDao;
use Biz\System\Service\LogService;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskService;
use Biz\User\Service\StatusService;

class CourseDeleteServiceImpl extends BaseService implements CourseDeleteService
{
    public function deleteCourseSet($courseSetId)
    {
        //XXX 这里仅处理删除逻辑，不对能否删除做判断
        try {
            $this->beginTransaction();

            $this->deleteCourseSetMaterial($courseSetId);

            $this->deleteCourseSetCourse($courseSetId);

            $this->getCourseSetDao()->delete($courseSetId);

            $this->commit();

            return $courseSetId;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    protected function deleteCourseSetMaterial($courseSetId)
    {
        $this->getMaterialService()->deleteMaterialsByCourseSetId($courseSetId, 'course');
    }

    protected function deleteCourseSetCourse($courseSetId)
    {
        $courses = $this->getCourseDao()->findByCourseSetIds([$courseSetId]);
        if (empty($courses)) {
            return;
        }

        foreach ($courses as $course) {
            $this->deleteCourse($course['id']);
        }
    }

    protected function deleteAttachment($targetId)
    {
        $conditions = [
            'targetId' => $targetId,
            'targetTypes' => ['question.stem', 'question.analysis'],
            'type' => 'attachment',
        ];

        $attachments = $this->getUploadFileService()->searchUseFiles($conditions);

        if (!$attachments) {
            return true;
        }

        foreach ($attachments as $attachment) {
            $this->getUploadFileService()->deleteUseFile($attachment['id']);
        }
    }

    public function deleteCourse($courseId)
    {
        try {
            $this->beginTransaction();

            $this->deleteCourseMaterial($courseId);

            $this->deleteCourseChapter($courseId);

            $this->deleteTask($courseId);
            $this->deleteTaskResult($courseId);

            $this->deleteCourseMember($courseId);

            $this->deleteCourseJob($courseId);

            $this->deleteCourseNote($courseId);

            $this->deleteCourseThread($courseId);

            $this->deleteCourseReview($courseId);

            $this->deleteCourseFavorite($courseId);

            $this->deleteCourseAnnouncement($courseId);

            $this->deleteCourseStatus($courseId);

            $this->deleteCourseCoversation($courseId);

            $this->updateMobileSetting($courseId);

            $this->getCourseDao()->delete($courseId);

            $this->commit();

            return $courseId;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    protected function updateMobileSetting($courseId)
    {
        $courseGrids = $this->getSettingService()->get('operation_course_grids', []);
        if (empty($courseGrids) || empty($courseGrids['courseIds'])) {
            return;
        }

        $courseIds = explode(',', $courseGrids['courseIds']);
        if (!in_array($courseId, $courseIds)) {
            return;
        }

        $operationMobile = $this->getSettingService()->get('operation_mobile', []);
        $settingMobile = $this->getSettingService()->get('mobile', []);

        $courseIds = array_diff($courseIds, [$courseId]);

        $mobile = array_merge($operationMobile, $settingMobile, $courseIds);

        $this->getSettingService()->set('operation_course_grids', ['courseIds' => implode(',', $courseIds)]);
        $this->getSettingService()->set('operation_mobile', $operationMobile);
        $this->getSettingService()->set('mobile', $mobile);
    }

    protected function deleteCourseMaterial($courseId)
    {
        $this->getMaterialService()->deleteMaterialsByCourseId($courseId, 'course');
    }

    protected function deleteCourseChapter($courseId)
    {
        $this->getChapterDao()->deleteChaptersByCourseId($courseId);
    }

    protected function deleteTask($courseId)
    {
        $tasks = $this->getTaskService()->findTasksByCourseId($courseId);
        if (!empty($tasks)) {
            $this->getTaskDao()->deleteByCourseId($courseId);
            foreach ($tasks as $task) {
                //delete activity
                $this->getActivityService()->deleteActivity($task['activityId']);
                $this->deleteJob($task);
            }
        }
    }

    protected function deleteTaskResult($courseId)
    {
        $this->getTaskResultDao()->deleteByCourseId($courseId);
    }

    protected function deleteCourseMember($courseId)
    {
        $this->getMemberDao()->deleteByCourseId($courseId);
    }

    protected function deleteJob($task)
    {
        if ('live' != $task['type']) {
            return;
        }
        //当前系统已不存在这个job PushNotificationOneHourJob_lesson_taskId
        $this->getSchedulerService()->deleteJobByName('PushNotificationOneHourJob_lesson_'.$task['id']);
        $this->getSchedulerService()->deleteJobByName('LiveCourseStartNotifyJob_liveLesson_'.$task['id']);
        $this->getSchedulerService()->deleteJobByName('SmsSendOneDayJob_task_'.$task['id']);
        $this->getSchedulerService()->deleteJobByName('SmsSendOneHourJob_task_'.$task['id']);
    }

    protected function deleteCourseJob($courseId)
    {
        $this->getCourseJobDao()->deleteByTypeAndCourseId('refresh_learning_progress', $courseId);
    }

    protected function deleteCourseNote($courseId)
    {
        $notes = $this->getNoteDao()->search(['courseId' => $courseId], [], 0, PHP_INT_MAX);
        if (empty($notes)) {
            return;
        }

        foreach ($notes as $note) {
            $this->getNoteLikeDao()->deleteByNoteId($note['id']);
        }
        $this->getNoteDao()->deleteByCourseId($courseId);
    }

    protected function deleteCourseThread($courseId)
    {
        $this->getThreadPostDao()->deleteByCourseId($courseId);
        $this->getThreadDao()->deleteByCourseId($courseId);
    }

    protected function deleteCourseReview($courseId)
    {
        $this->getReviewDao()->deleteByTargetTypeAndTargetId('course', $courseId);
    }

    protected function deleteCourseFavorite($courseId)
    {
        $this->getFavoriteDao()->deleteByTargetTypeAndsTargetId('course', $courseId);
    }

    public function deleteCourseAnnouncement($courseId)
    {
        $announcements = $this->getAnnouncementService()->searchAnnouncements(['targetType' => 'course', 'targetId' => $courseId], [], 0, PHP_INT_MAX);
        if (empty($announcements)) {
            return;
        }

        foreach ($announcements as $announcement) {
            $this->getAnnouncementService()->deleteAnnouncement($announcement['id']);
        }
    }

    protected function deleteCourseStatus($courseId)
    {
        $this->getStatusService()->deleteStatusesByCourseId($courseId);
    }

    protected function deleteCourseCoversation($courseId)
    {
        $this->getConversationService()->deleteConversationByTargetIdAndTargetType($courseId, 'course');
        $this->getConversationService()->deleteConversationByTargetIdAndTargetType($courseId, 'course-push');
    }

    /**
     * @return CourseSetDao
     */
    protected function getCourseSetDao()
    {
        return $this->createDao('Course:CourseSetDao');
    }

    /**
     * @return CourseDao
     */
    protected function getCourseDao()
    {
        return $this->createDao('Course:CourseDao');
    }

    protected function getTaskDao()
    {
        return $this->createDao('Task:TaskDao');
    }

    /**
     * @return MaterialService
     */
    protected function getMaterialService()
    {
        return $this->createService('Course:MaterialService');
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    /**
     * @return CourseChapterDao
     */
    protected function getChapterDao()
    {
        return $this->createDao('Course:CourseChapterDao');
    }

    /**
     * @return CourseMemberDao
     */
    protected function getMemberDao()
    {
        return $this->createDao('Course:CourseMemberDao');
    }

    protected function getTaskResultDao()
    {
        return $this->createDao('Task:TaskResultDao');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    protected function getQuestionDao()
    {
        return $this->createDao('Question:QuestionDao');
    }

    /**
     * @return CourseNoteDao
     */
    protected function getNoteDao()
    {
        return $this->createDao('Course:CourseNoteDao');
    }

    /**
     * @return CourseNoteLikeDao
     */
    protected function getNoteLikeDao()
    {
        return $this->createDao('Course:CourseNoteLikeDao');
    }

    /**
     * @return ThreadDao
     */
    protected function getThreadDao()
    {
        return $this->createDao('Course:ThreadDao');
    }

    /**
     * @return ThreadPostDao
     */
    protected function getThreadPostDao()
    {
        return $this->createDao('Course:ThreadPostDao');
    }

    /**
     * @return ReviewDao
     */
    protected function getReviewDao()
    {
        return $this->createDao('Review:ReviewDao');
    }

    protected function getCourseJobDao()
    {
        return $this->createDao('Course:CourseJobDao');
    }

    /**
     * @return FavoriteDao
     */
    protected function getFavoriteDao()
    {
        return $this->createDao('Favorite:FavoriteDao');
    }

    /**
     * @return AnnouncementService
     */
    protected function getAnnouncementService()
    {
        return $this->createService('Announcement:AnnouncementService');
    }

    /**
     * @return StatusService
     */
    protected function getStatusService()
    {
        return $this->createService('User:StatusService');
    }

    /**
     * @return ConversationService
     */
    protected function getConversationService()
    {
        return $this->createService('IM:ConversationService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    protected function getSchedulerService()
    {
        return $this->createService('Scheduler:SchedulerService');
    }

    /**
     * @return ActivityService
     */
    public function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }
}
