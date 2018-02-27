<?php

namespace Biz\Course\Service\Impl;

use Biz\BaseService;
use Biz\Course\Dao\CourseDao;
use Biz\Course\Dao\ReviewDao;
use Biz\Course\Dao\ThreadDao;
use Biz\Course\Dao\FavoriteDao;
use Biz\Course\Dao\CourseSetDao;
use Biz\Course\Dao\CourseNoteDao;
use Biz\Course\Dao\ThreadPostDao;
use Biz\Task\Service\TaskService;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Dao\CourseMemberDao;
use Biz\User\Service\StatusService;
use Biz\Course\Dao\CourseChapterDao;
use Biz\Course\Dao\CourseNoteLikeDao;
use Biz\System\Service\SettingService;
use Biz\IM\Service\ConversationService;
use Biz\Course\Service\CourseDeleteService;
use Biz\Testpaper\Service\TestpaperService;
use Biz\Announcement\Service\AnnouncementService;

class CourseDeleteServiceImpl extends BaseService implements CourseDeleteService
{
    public function deleteCourseSet($courseSetId)
    {
        //XXX 这里仅处理删除逻辑，不对能否删除做判断
        try {
            $this->beginTransaction();

            $this->deleteCourseSetMaterial($courseSetId);

            $this->deleteCourseSetCourse($courseSetId);

            $this->deleteTestpaper($courseSetId);

            $this->deleteQuestion($courseSetId);

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
        $this->getLogService()->info('course', 'delete_material', "删除课程(#{$courseSetId})的资料");
    }

    protected function deleteCourseSetCourse($courseSetId)
    {
        $courses = $this->getCourseDao()->findByCourseSetIds(array($courseSetId));
        if (empty($courses)) {
            return;
        }

        foreach ($courses as $course) {
            $this->deleteCourse($course['id']);
        }
    }

    protected function deleteQuestion($courseSetId)
    {
        $questions = $this->getQuestionDao()->findQuestionsByCourseSetId($courseSetId);
        if (empty($questions)) {
            return;
        }

        $this->getQuestionDao()->deleteByCourseSetId($courseSetId);

        foreach ($questions as $question) {
            $this->deleteAttachment($question['id']);
        }
    }

    protected function deleteAttachment($targetId)
    {
        $conditions = array(
            'targetId' => $targetId,
            'targetTypes' => array('question.stem', 'question.analysis'),
            'type' => 'attachment',
        );

        $attachments = $this->getUploadFileService()->searchUseFiles($conditions);

        if (!$attachments) {
            return true;
        }

        foreach ($attachments as $attachment) {
            $this->getUploadFileService()->deleteUseFile($attachment['id']);
        }
    }

    protected function deleteTestpaper($courseSetId)
    {
        $testpapers = $this->getTestpaperService()->searchTestpapers(array('courseSetId' => $courseSetId), array(), 0, PHP_INT_MAX);
        if (empty($testpapers)) {
            return;
        }

        $testpaperIds = ArrayToolkit::column($testpapers, 'id');
        $this->getTestpaperService()->deleteTestpapers($testpaperIds);
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
        $courseGrids = $this->getSettingService()->get('operation_course_grids', array());
        if (empty($courseGrids) || empty($courseGrids['courseIds'])) {
            return;
        }

        $courseIds = explode(',', $courseGrids['courseIds']);
        if (!in_array($courseId, $courseIds)) {
            return;
        }

        $operationMobile = $this->getSettingService()->get('operation_mobile', array());
        $settingMobile = $this->getSettingService()->get('mobile', array());

        $courseIds = array_diff($courseIds, array($courseId));

        $mobile = array_merge($operationMobile, $settingMobile, $courseIds);

        $this->getSettingService()->set('operation_course_grids', array('courseIds' => implode(',', $courseIds)));
        $this->getSettingService()->set('operation_mobile', $operationMobile);
        $this->getSettingService()->set('mobile', $mobile);
        $this->getLogService()->info('system', 'update_settings', '更新移动客户端设置', $mobile);
    }

    protected function deleteCourseMaterial($courseId)
    {
        $this->getMaterialService()->deleteMaterialsByCourseId($courseId, 'course');
        $this->getLogService()->info('course', 'delete_material', "删除计划(#{$courseId})下的资料");
    }

    protected function deleteCourseChapter($courseId)
    {
        $this->getChapterDao()->deleteChaptersByCourseId($courseId);
        $this->getLogService()->info('course', 'delete_chapter', "删除计划(#{$courseId})下的章节");
    }

    protected function deleteTask($courseId)
    {
        $tasks = $this->getTaskService()->findTasksByCourseId($courseId);
        if (!empty($tasks)) {
            $this->getTaskDao()->deleteByCourseId($courseId);
            foreach ($tasks as $task) {
                $this->getLogService()->info('course', 'delete_task', "删除任务《{$task['title']}》({$task['id']})", $task);
                //delete activity
                $this->getActivityService()->deleteActivity($task['activityId']);
                $this->deleteJob($task);
            }
        }
    }

    protected function deleteTaskResult($courseId)
    {
        $this->getTaskResultDao()->deleteByCourseId($courseId);
        $this->getLogService()->info('course', 'delete_taskLearn', "删除计划(#{$courseId})下的全部任务学习记录");
    }

    protected function deleteCourseMember($courseId)
    {
        $this->getMemberDao()->deleteByCourseId($courseId);
        $this->getLogService()->info('course', 'delete_member', "删除计划(#{$courseId})下的学员");
    }

    protected function deleteJob($task)
    {
        if ($task['type'] != 'live') {
            return;
        }
        //当前系统已不存在这个job PushNotificationOneHourJob_lesson_taskId
        $this->getSchedulerService()->deleteJobByName('PushNotificationOneHourJob_lesson_'.$task['id']);
        $this->getSchedulerService()->deleteJobByName('LiveCourseStartNotifyJob_liveLesson_'.$task['id']);
        $this->getSchedulerService()->deleteJobByName('SmsSendOneDayJob_task_'.$task['id']);
        $this->getSchedulerService()->deleteJobByName('SmsSendOneHourJob_task_'.$task['id']);

        $this->getLogService()->info('course', 'delete_course_job', '删除直播任务的推送及发送短信的定时任务');
    }

    protected function deleteCourseJob($courseId)
    {
        $this->getCourseJobDao()->deleteByTypeAndCourseId('refresh_learning_progress', $courseId);
        $this->getLogService()->info('course', 'delete_course_job', "删除计划(#{$courseId})下的更新学员学习进度的job");
    }

    protected function deleteCourseNote($courseId)
    {
        $notes = $this->getNoteDao()->search(array('courseId' => $courseId), array(), 0, PHP_INT_MAX);
        if (empty($notes)) {
            return;
        }

        foreach ($notes as $note) {
            $this->getNoteLikeDao()->deleteByNoteId($note['id']);
        }
        $this->getNoteDao()->deleteByCourseId($courseId);

        $this->getLogService()->info('course', 'delete_note', "删除计划(#{$courseId})下的笔记");
    }

    protected function deleteCourseThread($courseId)
    {
        $this->getThreadPostDao()->deleteByCourseId($courseId);
        $this->getThreadDao()->deleteByCourseId($courseId);

        $this->getLogService()->info('course', 'delete_thread', "删除计划(#{$courseId})下的话题及回复");
    }

    protected function deleteCourseReview($courseId)
    {
        $this->getReviewDao()->deleteByCourseId($courseId);
        $this->getLogService()->info('course', 'delete_review', "删除计划(#{$courseId})下的评价");
    }

    protected function deleteCourseFavorite($courseId)
    {
        $this->getFavoriteDao()->deleteByCourseId($courseId);
        $this->getLogService()->info('course', 'delete_course_favorite', "删除关于计划(#{$courseId})下的收藏");
    }

    public function deleteCourseAnnouncement($courseId)
    {
        $announcements = $this->getAnnouncementService()->searchAnnouncements(array('targetType' => 'course', 'targetId' => $courseId), array(), 0, PHP_INT_MAX);
        if (empty($announcements)) {
            return;
        }

        foreach ($announcements as $announcement) {
            $this->getAnnouncementService()->deleteAnnouncement($announcement['id']);
        }

        $this->getLogService()->info('course', 'delete_announcement', "删除计划(#{$courseId})下的公告");
    }

    protected function deleteCourseStatus($courseId)
    {
        $this->getStatusService()->deleteStatusesByCourseId($courseId);
        $this->getLogService()->info('course', 'delete_status', "删除计划(#{$courseId})的学员动态");
    }

    protected function deleteCourseCoversation($courseId)
    {
        $this->getConversationService()->deleteConversationByTargetIdAndTargetType($courseId, 'course');
        $this->getConversationService()->deleteConversationByTargetIdAndTargetType($courseId, 'course-push');

        $this->getLogService()->info('course', 'delete_coversation', "删除计划(#{$courseId})的会话");
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
        return $this->createDao('Course:ReviewDao');
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
        return $this->createDao('Course:FavoriteDao');
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
