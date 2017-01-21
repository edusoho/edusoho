<?php

namespace Biz\Course\Service\Impl;

use Biz\Announcement\Dao\AnnouncementDao;
use Biz\BaseService;
use Biz\Course\Dao\CourseChapterDao;
use Biz\Course\Dao\CourseDao;
use Biz\Course\Dao\CourseMaterialDao;
use Biz\Course\Dao\CourseMemberDao;
use Biz\Course\Dao\CourseNoteDao;
use Biz\Course\Dao\CourseNoteLikeDao;
use Biz\Course\Dao\CourseSetDao;
use Biz\Course\Dao\FavoriteDao;
use Biz\Course\Dao\ReviewDao;
use Biz\Course\Dao\ThreadDao;
use Biz\Course\Dao\ThreadPostDao;
use Biz\Course\Service\CourseDeleteService;
use Biz\IM\Dao\ConversationDao;
use Biz\Question\Dao\QuestionDao;
use Biz\Task\Service\TaskService;
use Biz\Testpaper\Dao\TestpaperDao;
use Biz\Testpaper\Dao\TestpaperItemDao;
use Biz\User\Dao\StatusDao;

class CourseDeleteServiceImpl extends BaseService implements CourseDeleteService
{
    public function deleteCourseSet($courseSetId)
    {
        try {
            $this->beginTransaction();
            //todo tryManageCourseSet...

            //delete course_material
            $this->getMaterialDao()->deleteByCourseSetId($courseSetId, 'course');
            //delete testpaper
            $this->getTestpaperDao()->deleteByCourseSetId($courseSetId);
            //delete courses
            $courses = $this->getCourseDao()->findByCourseSetIds(array($courseSetId));

            if (!empty($courses)) {
                foreach ($courses as $course) {
                    $this->deleteCourse($course['id']);
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
            //todo tryManageCourse
//            $course = $this->getCourseDao()->get($courseId);
            //delete course_material
            $this->getMaterialDao()->deleteByCourseId($courseId, 'course');
            //delete testpaper & testpaperItem
            $testpapers = $this->getTestpaperDao()->search(array('courseId' => $courseId), array(), 0, PHP_INT_MAX);
            if(!empty($testpapers)){
                foreach ($testpapers as $testpaper){
                    $this->getTestpaperItemDao()->deleteItemsByTestpaperId($testpaper['id']);
                    $this->getTestpaperDao()->delete($testpaper['id']);
                }
            }

            //delete course_chapter
            $this->getChapterDao()->deleteChaptersByCourseId($courseId);
            //delete course_member
            $this->getMemberDao()->deleteByCourseId($courseId);
            //delete task & activity & activityConfig
            $tasks = $this->getTaskService()->findTasksByCourseId($courseId);
            if(!empty($tasks)){
                foreach ($tasks as $task){
                    //delete task and activity
                    $this->getTaskService()->deleteTask($task['id']);
                }
            }
            //delete question
            $questions = $this->getQuestionDao()->search(array('courseId' => $courseId), array(), 0, PHP_INT_MAX);
            if(!empty($questions)){
                foreach ($questions as $question){
                    $this->getQuestionDao()->deleteSubQuestions($question['id']);
                    $this->getQuestionDao()->delete($question['id']);
                }
            }

            //delete course_note
            $this->getNoteLikeDao()->deleteByCourseId($courseId);
            $this->getNoteDao()->deleteByCourseId($courseId);
            //delete course_thread
            $this->getThreadPostDao()->deleteByCourseId($courseId);
            $this->getThreadDao()->deleteByCourseId($courseId);
            //delete course_review
            $this->getReviewDao()->deleteByCourseId($courseId);
            //delete course_favorite
            $this->getFavoriteDao()->deleteByCourseId($courseId);
            //delete course_announcement
            $this->getAnnouncementDao()->deleteByTargetIdAndTargetType($courseId, 'course');
            //delete status
            $this->getStatusDao()->deleteByCourseId($courseId);
            //delete conversation ?
            $this->getConversationDao()->deleteByTargetIdAndTargetType($courseId, 'course');
            $this->getConversationDao()->deleteByTargetIdAndTargetType($courseId, 'course-push');
            //delete message_conversation ? todo
            //delete course
            $this->getCourseDao()->delete($courseId);

            $this->commit();

            return $courseId;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * @return CourseSetDao
     */
    protected function getCourseSetDao()
    {
        return $this->createService('Course:CourseSetDao');
    }

    /**
     * @return CourseDao
     */
    protected function getCourseDao()
    {
        return $this->createService('Course:CourseDao');
    }

    /**
     * @return CourseMaterialDao
     */
    protected function getMaterialDao()
    {
        return $this->createService('Course:CourseMaterialDao');
    }

    /**
     * @return TestpaperDao
     */
    protected function getTestpaperDao()
    {
        return $this->createService('Testpaper:TestpaperDao');
    }

    /**
     * @return TestpaperItemDao
     */
    protected function getTestpaperItemDao()
    {
        return $this->createService('Testpaper:TestpaperItemDao');
    }

    /**
     * @return CourseChapterDao
     */
    protected function getChapterDao()
    {
        return $this->createService('Course:CourseChapterDao');
    }

    /**
     * @return CourseMemberDao
     */
    protected function getMemberDao()
    {
        return $this->createService('Course:CourseMemberDao');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return QuestionDao
     */
    protected function getQuestionDao()
    {
        return $this->createService('Question:QuestionDao');
    }

    /**
     * @return CourseNoteDao
     */
    protected function getNoteDao()
    {
        return $this->createService('Course:CourseNoteDao');
    }

    /**
     * @return CourseNoteLikeDao
     */
    protected function getNoteLikeDao()
    {
        return $this->createService('Course:CourseNoteLikeDao');
    }

    /**
     * @return ThreadDao
     */
    protected function getThreadDao()
    {
        return $this->createService('Course:ThreadDao');
    }

    /**
     * @return ThreadPostDao
     */
    protected function getThreadPostDao()
    {
        return $this->createService('Course:ThreadPostDao');
    }

    /**
     * @return ReviewDao
     */
    protected function getReviewDao()
    {
        return $this->createService('Course:ReviewDao');
    }

    /**
     * @return FavoriteDao
     */
    protected function getFavoriteDao()
    {
        return $this->createService('Course:FavoriteDao');
    }

    /**
     * @return AnnouncementDao
     */
    protected function getAnnouncementDao()
    {
        return $this->createService('Announcement:AnnouncementDao');
    }

    /**
     * @return StatusDao
     */
    protected function getStatusDao()
    {
        return $this->createService('User:StatusDao');
    }

    /**
     * @return ConversationDao
     */
    protected function getConversationDao()
    {
        return $this->createService('IM:ConversationDao');
    }
}

