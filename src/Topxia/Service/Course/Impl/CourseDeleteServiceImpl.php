<?php
namespace Topxia\Service\Course\Impl;

use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\File\File;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Course\CourseDeleteService;

class CourseDeleteServiceImpl extends BaseService implements CourseDeleteService
{
	public function delete($courseId)
    {
    	    	
    	$this->getCourseDao()->getConnection()->beginTransaction();
    	try{
    		$course = $this->getCourseService()->getCourse($courseId);

            $this->deleteQuestions($course);

            $this->deleteTestpapers($course);

            $this->deleteMaterials($course);

            $this->deleteChapters($course);

            $this->deleteDrafts($course);

            $this->deleteLessons($course);

            $this->deleteLessonLearns($course);
            
            $this->deleteLessonReplays($course);

            $this->deleteLessonViews($course);

            $code = 'Homework';
            $homework = $this->getAppService()->findInstallApp($code);
            $isDeleteHomework = $homework && version_compare($homework['version'], "1.0.4", ">=");

            if($isDeleteHomework){

                $this->deleteHomework($course);

                $this->deleteExercise($course);
            }

            $this->deleteFavorites($course);

            $this->deleteNotes($course);

            $this->deleteThreads($course);

            $this->deleteReviews($course);

            $this->deleteAnnouncements($course);

            $this->deleteStatuses($course);

            $this->deleteMembers($course);

            $this->deleteCourses($course);

            $this->getCourseDao()->getConnection()->commit();

    	} catch (\Exception $e) {

    		$this->getCourseDao()->getConnection()->rollback();
            
            throw $e;
    	}
    }

    protected function deleteQuestions($course)
    {
        $questions = $this->getQuestionDao()->findQuestionsByCourseId($course['id']);
        foreach ($questions as $question) {
            $this->getQuestionDao()->deleteQuestion($question['id']);
            $this->getQuestionFavoriteDao()->deleteFavoriteByQuestionId($question['id']);
            $questionLog = "删除课程《{$course['title']}》(#{$course['id']})的问题";
            $this->getLogService()->info('question', 'delete', $questionLog);
        }
    }

    protected function deleteTestpapers($course)
    {
        $testpapers = $this->getTestpaperService()->findAllTestpapersByTarget($course['id']);
        foreach ($testpapers as $testpaper) {
            $this->getTestpaperResultDao()->deleteTestpaperResultByTestpaperId($testpaper['id']);
            $this->getTestpaperItemResultDao()->deleteTestpaperItemResultByTestpaperId($testpaper['id']);
            $this->getTestpaperItemDao()->deleteItemsByTestpaperId($testpaper['id']);
            $this->getTestpaperDao()->deleteTestpaper($testpaper['id']);
            //删除完成试卷动态
            $this->getStatusDao()->deleteStatusesByCourseIdAndTypeAndObject(0,'finished_testpaper','testpaper',$testpaper['id']);
            $testpaperLog = "删除课程《{$course['title']}》(#{$course['id']})的试卷　{$testpaper['name']}";
            $this->getLogService()->info('testpaper', 'delete', $testpaperLog);
        }

    }

    protected function deleteMaterials($course)
    {
        $materials = $this->getMaterialDao()->findCourseMaterialsByCourseId($course['id']);
        foreach ($materials as $material) {
            $this->getMaterialDao()->deleteMaterial($material['id']);
            $materialLog = "删除课程《{$course['title']}》(#{$course['id']})的课时资料　{$material['title']}";
            $this->getLogService()->info('material', 'delete', $materialLog);
        }
    }

    protected function deleteChapters($course)
    {
        $chapters = $this->getCourseChapterDao()->findChaptersByCourseId($course['id']);
        foreach ($chapters as $chapter) {
            $this->getCourseChapterDao()->deleteChapter($chapter['id']);
            $chapterLog = "删除课程《{$course['title']}》(#{$course['id']})的课时章/节　{$chapter['title']}";
            $this->getLogService()->info('chapter', 'delete', $chapterLog);
        }
    }

    protected function deleteDrafts($course)
    {
        $drafts = $this->getDraftDao()->findDraftsByCourseId($course['id']);
        foreach ($drafts as $draft) {
            $this->getDraftDao()->deleteDraft($draft['id']);
            $draftLog = "删除课程《{$course['title']}》(#{$course['id']})的草稿　{$draft['title']}";
            $this->getLogService()->info('draft', 'delete', $draftLog);
        }
    }

    protected function deleteLessons($course)
    {
        $lessons = $this->getLessonDao()->findLessonsByCourseId($course['id']);
        foreach ($lessons as $lesson) {
            $this->getLessonDao()->deleteLesson($lesson['id']);
            $lessonLog = "删除课程《{$course['title']}》(#{$course['id']})的课时章/节　{$lesson['title']}";
            $this->getLogService()->info('lesson', 'delete', $lessonLog);
        }
    }

    protected function deleteLessonLearns($course)
    {
        $lessonLearns = $this->getLessonLearnDao()->findLearnsByCourseId($course['id']);
        foreach ($lessonLearns as $lessonLearn) {
           $this->getLessonLearnDao()->deleteLearn($lessonLearn['id']); 
        }
    }

    protected function deleteLessonReplays($course)
    {
        $LessonReplays = $this->getCourseLessonReplayDao()->findCourseLessonReplaysByCourseId($course['id']);
        foreach ($LessonReplays as  $LessonReplay) {
            $this->getCourseLessonReplayDao()->deleteCourseLessonReplay($LessonReplay['id']); 
            $LessonReplayLog = "删除课程《{$course['title']}》(#{$course['id']})的录播　{$LessonReplay['title']}";
            $this->getLogService()->info('LessonReplay', 'delete', $LessonReplayLog);
        }
    }

    protected function deleteLessonViews($course)
    {
        $lessonViews = $this->getLessonViewDao()->findLessonViewsByCourseId($course['id']);
        foreach ($lessonViews as $lessonView) {
            $this->getLessonViewDao()->deleteLessonView($lessonView['id']);
        }
    }

    protected function deleteHomework($course)
    {
        $homeworks = $this->getHomeworkDao()->findHomeworksByCourseId($course['id']);
        foreach ($homeworks as $homework) {  
            $this->getHomeworkResultDao()->deleteResultsByHomeworkId($homework['id']);
            $this->getHomeworkItemResultDao()->deleteItemResultsByHomeworkId($homework['id']);
            $this->getHomeworkItemDao()->deleteItemsByHomeworkId($homework['id']);
            $this->getHomeworkDao()->deleteHomework($homework['id']);
            //删除完成作业动态
            $this->getStatusDao()->deleteStatusesByCourseIdAndTypeAndObject(0,'finished_homework','homework',$homework['id']);
        }
        $homeworkLog = "删除课程《{$course['title']}》(#{$course['id']})的作业";
        $this->getLogService()->info('homework', 'delete', $homeworkLog);
    }

    protected function deleteExercise($course)
    {
        $exercises = $this->getExerciseDao()->findExercisesByCourseId($course['id']);
        foreach ($exercises as $exercise) {
            $this->getExerciseResultDao()->deleteExerciseResultByExerciseId($exercise['id']);
            $this->getExerciseItemResultDao()->deleteItemResultByExerciseId($exercise['id']);
            $this->getExerciseItemDao()->deleteItemByExerciseId($exercise['id']);
            $this->getExerciseDao()->deleteExercise($exercise['id']);
            //删除完成练习的动态
            $this->getStatusDao()->deleteStatusesByCourseIdAndTypeAndObject(0,'finished_exercise','exercise',$exercise['id']);
        }
        $exerciseLog = "删除课程《{$course['title']}》(#{$course['id']})的练习";
        $this->getLogService()->info('exercise', 'delete', $exerciseLog);
    }

    protected function deleteFavorites($course)
    {
        $favorites = $this->getFavoriteDao()->findCourseFavoritesByCourseId($course['id']);
        foreach ($favorites as $favorite) {
            $this->getFavoriteDao()->deleteFavorite($favorite['id']);
        }
        $favoriteLog = "删除课程《{$course['title']}》(#{$course['id']})的课程收藏";
        $this->getLogService()->info('favorite', 'delete', $favoriteLog);
    }

    protected function deleteNotes($course)
    {
        $notes = $this->getCourseNoteDao()->findNotesByCourseId($course['id']);
        foreach ($notes as $note) {
            $this->getCourseNoteLikeDao()->deleteNoteLikesByNoteId($note['id']);
            $this->getCourseNoteDao()->deleteNote($note['id']);
        }
        $noteLog = "删除课程《{$course['title']}》(#{$course['id']})的课程笔记";
        $this->getLogService()->info('note', 'delete', $noteLog);
    }

    protected function deleteThreads($course)
    {
        $threads = $this->getThreadDao()->findCourseThreadsByCourseId($course['id']);
        foreach ($threads as $thread) {
            $this->getThreadPostDao()->deletePostsByThreadId($thread['id']);
            $this->getThreadDao()->deleteThread($thread['id']);
            $threadLog = "删除课程《{$course['title']}》(#{$course['id']})的话题 {$thread['title']}";
            $this->getLogService()->info('thread', 'delete', $threadLog);
        }
    }

    protected function deleteReviews($course)
    {
        $reviews = $this->getReviewDao()->findCourseReviewsByCourseId($course['id']);
        foreach ($reviews as $review) {
            $this->getReviewDao()->deleteReview($review['id']);
        }
        $reviewLog = "删除课程《{$course['title']}》(#{$course['id']})的评价";
        $this->getLogService()->info('review', 'delete', $reviewLog);
    }

    protected function deleteAnnouncements($course)
    {
        $announcements = $this->getAnnouncementDao()->findAnnouncementsByTargetTypeAndTargetId('course',$course['id']);
        foreach ($announcements as $announcement) {
            $this->getAnnouncementDao()->deleteAnnouncement($announcement['id']);
        }
        $announcementLog = "删除课程《{$course['title']}》(#{$course['id']})的公告";
        $this->getLogService()->info('announcement', 'delete', $announcementLog);
    }

    protected function deleteStatuses($course)
    {
        $statuses = $this->getStatusDao()->findStatusesByCourseId($course['id']);
        foreach ($statuses as $status) {
           $this->getStatusDao()->deleteStatus($status['id']);
        }
        $statusLog = "删除课程《{$course['title']}》(#{$course['id']})的动态";
        $this->getLogService()->info('status', 'delete', $statusLog);
    }

    protected function deleteMembers($course)
    {
        $this->getCourseMemberDao()->deleteMembersByCourseId($course['id']);
        $memberLog = "删除课程《{$course['title']}》(#{$course['id']})的成员";
        $this->getLogService()->info('member', 'delete', $memberLog);
    }

    protected function deleteCourses($course)
    {
        $this->getCourseDao()->deleteCourse($course['id']);
        $courseLog = "删除课程《{$course['title']}》(#{$course['id']})";
        $this->getLogService()->info('course', 'delete', $courseLog);
    }



    protected function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

    protected function getTestpaperService()
    {
        return $this->createService('Testpaper.TestpaperService');
    }

    protected function getAppService()
    {
        return $this->createService('CloudPlatform.AppService');
    }

    protected function getLogService()
    {
        return $this->createService('System.LogService');
    }

    protected function getCourseChapterDao()
    {
        return $this->createDao('Course.CourseChapterDao');
    }

    protected function getDraftDao ()
    {
        return $this->createDao('Course.DraftDao');
    }

    protected function getLessonDao()
    {
        return $this->createDao('Course.LessonDao');
    }

    protected function getLessonLearnDao ()
    {
        return $this->createDao('Course.LessonLearnDao');
    }

    protected function getCourseLessonReplayDao ()
    {
        return $this->createDao('Course.CourseLessonReplayDao');
    }

    protected function getLessonViewDao ()
    {
        return $this->createDao('Course.LessonViewDao');
    }

    protected function getMaterialDao()
    {
        return $this->createDao('Course.CourseMaterialDao');
    }

    protected function getClassroomDao()
    {
        return $this->createDao('Classroom:Classroom.ClassroomDao');
    }

    protected function getQuestionDao()
    {
        return $this->createDao('Question.QuestionDao');
    }

    protected function getQuestionFavoriteDao()
    {
        return $this->createDao('Question.QuestionFavoriteDao');
    }

    protected function getTestpaperResultDao()
    {
        return $this->createDao('Testpaper.TestpaperResultDao');
    }

    protected function getTestpaperItemResultDao(){
        
        return $this->createDao('Testpaper.TestpaperItemResultDao');
    }

    protected function getTestpaperItemDao(){
        return $this->createDao('Testpaper.TestpaperItemDao');
    }

    protected function getTestpaperDao()
    {
        return $this->createDao('Testpaper.TestpaperDao');
    }

    protected function getCourseDao()
    {
        return $this->createDao('Course.CourseDao');
    }

    protected function getHomeworkDao()
    {
        return $this->createDao('Homework:Homework.HomeworkDao');
    }

    protected function getHomeworkItemDao()
    {
        return $this->createDao('Homework:Homework.HomeworkItemDao');
    }

    protected function getHomeworkItemResultDao()
    {
        return $this->createDao('Homework:Homework.HomeworkItemResultDao');
    }

    protected function getHomeworkResultDao()
    {
        return $this->createDao('Homework:Homework.HomeworkResultDao');
    }

    protected function getExerciseDao()
    {
        return $this->createDao('Homework:Homework.ExerciseDao');
    }

    protected function getExerciseItemDao()
    {
        return $this->createDao('Homework:Homework.ExerciseItemDao');
    }

    protected function getExerciseItemResultDao()
    {
        return $this->createDao('Homework:Homework.ExerciseItemResultDao');
    }

    protected function getExerciseResultDao()
    {
        return $this->createDao('Homework:Homework.ExerciseResultDao');
    }

    protected function getFavoriteDao()
    {
        return $this->createDao('Course.FavoriteDao');
    }

    protected function getCourseNoteDao()
    {
        return $this->createDao('Course.CourseNoteDao');
    }

    protected function getCourseNoteLikeDao()
    {
        return $this->createDao('Course.CourseNoteLikeDao');
    }

    protected function getThreadDao()
    {
        return $this->createDao('Course.ThreadDao');
    }

    protected function getThreadPostDao()
    {
        return $this->createDao('Course.ThreadPostDao');
    }

    protected function getReviewDao()
    {
        return $this->createDao('Course.ReviewDao');
    }

    protected function getAnnouncementDao()
    {
        return $this->createDao('Announcement.AnnouncementDao');
    }

    protected function getStatusDao()
    {
        return $this->createDao('User.StatusDao');
    }

    protected function getCourseMemberDao()
    {
        return $this->createDao('Course.CourseMemberDao');
    }
}