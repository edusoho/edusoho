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
use Biz\Question\Service\QuestionService;
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

            //delete course_material
            $this->getMaterialService()->deleteMaterialsByCourseSetId($courseSetId, 'course');

            //delete courses
            $courses = $this->getCourseDao()->findByCourseSetIds(array($courseSetId));
            if (!empty($courses)) {
                foreach ($courses as $course) {
                    $this->deleteCourse($course['id']);

                    //delete course_member
                    $this->getMemberDao()->deleteByCourseId($course['id']);

                    //delete course
                    $this->getCourseDao()->delete($course['id']);
                }
            }

            //delete testpaper
            $testpapers = $this->getTestpaperService()->searchTestpapers(array('courseSetId' => $courseSetId), array(), 0, PHP_INT_MAX);
            if (!empty($testpapers)) {
                $testpaperIds = ArrayToolkit::column($testpapers, 'id');
                $this->getTestpaperService()->deleteTestpapers($testpaperIds);
            }

            //delete question
            $questions = $this->getQuestionService()->search(array('courseSetId' => $courseSetId), array(), 0, PHP_INT_MAX);
            if (!empty($questions)) {
                foreach ($questions as $question) {
                    $this->getQuestionService()->delete($question['id']);
                }
            }

            //delete courseSet
            $this->getCourseSetDao()->delete($courseSetId);

            $this->commit();

            return $courseSetId;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function deleteCourse($courseId)
    {
        try {
            $this->beginTransaction();

            //delete course_material
            $this->getMaterialService()->deleteMaterialsByCourseId($courseId, 'course');

            //delete course_chapter
            $this->getChapterDao()->deleteChaptersByCourseId($courseId);

            //delete task & activity & activityConfig
            $tasks = $this->getTaskService()->findTasksByCourseId($courseId);
            if (!empty($tasks)) {
                foreach ($tasks as $task) {
                    //delete task and activity
                    $existTask = $this->getTaskService()->getTask($task['id']);
                    if ($existTask) {
                        $this->getTaskService()->deleteTask($task['id']);
                    }
                }
            }

            //delete course_note
            $notes = $this->getNoteDao()->search(array('courseId' => $courseId), array(), 0, PHP_INT_MAX);
            if (!empty($notes)) {
                foreach ($notes as $note) {
                    $this->getNoteLikeDao()->deleteByNoteId($note['id']);
                }
                $this->getNoteDao()->deleteByCourseId($courseId);
            }

            //delete course_thread
            $this->getThreadPostDao()->deleteByCourseId($courseId);
            $this->getThreadDao()->deleteByCourseId($courseId);

            //delete course_review
            $this->getReviewDao()->deleteByCourseId($courseId);

            //delete course_favorite
            $this->getFavoriteDao()->deleteByCourseId($courseId);

            //delete course_announcement
            $announcements = $this->getAnnouncementService()->searchAnnouncements(array('targetType' => 'course', 'targetId' => $courseId), array(), 0, PHP_INT_MAX);
            if (!empty($announcements)) {
                foreach ($announcements as $announcement) {
                    $this->getAnnouncementService()->deleteAnnouncement($announcement['id']);
                }
                $announcementLog = "删除课程(#{$courseId})的公告";
                $this->getLogService()->info('course', 'delete_announcement', $announcementLog);
            }

            //delete status
            $this->getStatusService()->deleteStatusesByCourseId($courseId);
            $statusLog = "删除课程(#{$courseId})的动态";
            $this->getLogService()->info('course', 'delete_status', $statusLog);

            //delete conversation
            $this->getConversationService()->deleteConversationByTargetIdAndTargetType($courseId, 'course');
            $this->getConversationService()->deleteConversationByTargetIdAndTargetType($courseId, 'course-push');

            //delete message_conversation ? todo

            //delete mobile setting
            $this->updateMobileSetting($courseId);

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

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return QuestionService
     */
    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
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
}
