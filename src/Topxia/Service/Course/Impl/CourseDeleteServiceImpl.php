<?php
namespace Topxia\Service\Course\Impl;

use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\File\File;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Course\CourseDeleteService;

class CourseDeleteServiceImpl extends BaseService implements CourseDeleteService
{
	public function delete($courseId, $type)
    {    	
        $course = $this->getCourseService()->getCourse($courseId);

        switch ($type) {

            case 'question':
                return $this->deleteQuestions($course);
                break;

            case 'testpaper':
                return $this->deleteTestpapers($course);
                break;

            case 'material':
                return $this->deleteMaterials($course);
                break;

            case 'chapter':
                return $this->deleteChapters($course);
                break;

            case 'draft':
                return $this->deleteDrafts($course);
                break;

            case 'lesson':
                return $this->deleteLessons($course);
                break;

            case 'lessonLearns':
                return $this->deleteLessonLearns($course);
                break;

            case 'lessonReplays':
                return $this->deleteLessonReplays($course);
                break;

            case 'lessonViews':
                return $this->deleteLessonViews($course);
                break;

            case 'homework':
                return $this->deleteHomework($course);
                break;

            case 'exercise':
                return $this->deleteExercise($course);
                break;

            case 'favorite':
                return $this->deleteFavorites($course);
                break;

            case 'note':
                return $this->deleteNotes($course);
                break;

            case 'thread':
                return $this->deleteThreads($course);
                break;

            case 'review':
                return $this->deleteReviews($course);
                break;

            case 'announcement':
                return $this->deleteAnnouncements($course);
                break;

            case 'status':
                return $this->deleteStatuses($course);
                break;

            case 'member':
                return $this->deleteMembers($course);
                break;

            case 'course':
                return $this->deleteCourses($course);
                break;   
            default:
                return;                  
                break;
        }
    }

    protected function deleteQuestions($course)
    {
        $questionCount = $this->getQuestionDao()->searchQuestionsCount(array('targetPrefix' => "course-{$course['id']}"));
        if($questionCount>0){
            $questions = $this->getQuestionDao()->searchQuestions(array('targetPrefix' => "course-{$course['id']}"), array('createdTime' ,'desc'), 0, 1000);
            foreach ($questions as $question) {
                $this->getQuestionDao()->deleteQuestion($question['id']);
                $this->getQuestionFavoriteDao()->deleteFavoriteByQuestionId($question['id']);
            }
            $questionLog = "删除课程《{$course['title']}》(#{$course['id']})的问题";
            $this->getLogService()->info('question', 'delete', $questionLog);
            $response = array('success'=>true,'message'=>'问题数据删除');
        }else{
            $response = array('success'=>false,'message'=>'问题数据查询失败');
        }

        return $response;
    }

    protected function deleteTestpapers($course)
    {
        $testpaperCount = $this->getTestpaperDao()->searchTestpapersCount(array('target'=>"course-{$course['id']}"));
        if($testpaperCount>0){
            $testpapers = $this->getTestpaperDao()->searchTestpapers(array('target'=>"course-{$course['id']}"),array('createdTime' ,'desc'),0,1000);
            foreach ($testpapers as $testpaper) {
                $this->getTestpaperResultDao()->deleteTestpaperResultByTestpaperId($testpaper['id']);
                $this->getTestpaperItemResultDao()->deleteTestpaperItemResultByTestpaperId($testpaper['id']);
                $this->getTestpaperItemDao()->deleteItemsByTestpaperId($testpaper['id']);
                $this->getTestpaperDao()->deleteTestpaper($testpaper['id']);
                //删除完成试卷动态
                $this->getStatusDao()->deleteStatusesByCourseIdAndTypeAndObject(0,'finished_testpaper','testpaper',$testpaper['id']);
            }
            $testpaperLog = "删除课程《{$course['title']}》(#{$course['id']})的试卷";
            $this->getLogService()->info('testpaper', 'delete', $testpaperLog);
            $response = array('success'=>true,'message'=>'试卷数据删除');
        }else{
            $response = array('success'=>false,'message'=>'试卷数据查询失败');
        }

        return $response;
    }

    protected function deleteMaterials($course)
    {
        $materialCount = $this->getMaterialDao()->getMaterialCountByCourseId($course['id']);
        if($materialCount>0){
            $materials = $this->getMaterialDao()->findMaterialsByCourseId($course['id'],0,1000);
            foreach ($materials as $material) {
                $this->getMaterialDao()->deleteMaterial($material['id']);
            }
            $materialLog = "删除课程《{$course['title']}》(#{$course['id']})的课时资料";
            $this->getLogService()->info('material', 'delete', $materialLog);
            $response = array('success'=>true,'message'=>'课时资料数据删除');
        }else{
            $response = array('success'=>false,'message'=>'课时资料数据查询失败');
        }

        return $response;  
    }

    protected function deleteChapters($course)
    {
        $chapterCount = $this->getCourseChapterDao()->findChaptersCountByCourseId($course['id']);
        if($chapterCount>0){
            $chapters = $this->getCourseChapterDao()->searchChapters(array('courseId'=>$course['id']),array('createdTime' ,'desc'),0,1000);
            foreach ($chapters as $chapter) {
                $this->getCourseChapterDao()->deleteChapter($chapter['id']);
            }
            $chapterLog = "删除课程《{$course['title']}》(#{$course['id']})的课时章/节";
            $this->getLogService()->info('chapter', 'delete', $chapterLog);
            $response = array('success'=>true,'message'=>'课时章节数据删除');
        }else{
            $response = array('success'=>false,'message'=>'课时章节数据查询失败');
        }

        return $response;

    }

    protected function deleteDrafts($course)
    {
        $draftCount = $this->getDraftDao()->findDraftsCountByCourseId($course['id']);
        if($draftCount>0){
            $drafts = $this->getDraftDao()->searchDrafts(array('courseId'=>$course['id']),array('createdTime' ,'desc'),0,1000);
            foreach ($drafts as $draft) {
                $this->getDraftDao()->deleteDraft($draft['id']);
            }
            $draftLog = "删除课程《{$course['title']}》(#{$course['id']})的草稿";
            $this->getLogService()->info('draft', 'delete', $draftLog);
            $response = array('success'=>true,'message'=>'课时草稿数据删除');
        }else{
            $response = array('success'=>false,'message'=>'课时草稿数据查询失败');
        }

        return $response;
    }

    protected function deleteLessons($course)
    {
        $lessonCount = $this->getLessonDao()->searchLessonCount(array('courseId'=>$course['id']));
        if($lessonCount>0){
            $lessons = $this->getLessonDao()->searchLessons(array('courseId'=>$course['id']),array('createdTime' ,'desc'),0,1000);
            foreach ($lessons as $lesson) {
                $this->getLessonDao()->deleteLesson($lesson['id']);
            }
            $lessonLog = "删除课程《{$course['title']}》(#{$course['id']})的课时";
            $this->getLogService()->info('lesson', 'delete', $lessonLog);
            $response = array('success'=>true,'message'=>'课时数据删除');
        }else{
            $response = array('success'=>false,'message'=>'课时数据查询失败');
        }

        return $response;
        
    }

    protected function deleteLessonLearns($course)
    {   
        $lessonLearnCount = $this->getLessonLearnDao()->searchLearnCount(array('courseId'=>$course['id']));
        if($lessonLearnCount>0){
            $lessonLearns = $this->getLessonLearnDao()->searchLearns(array('courseId'=>$course['id']),array('startTime' ,'desc'),0,1000);
            foreach ($lessonLearns as $lessonLearn) {
               $this->getLessonLearnDao()->deleteLearn($lessonLearn['id']); 
            }
            $response = array('success'=>true,'message'=>'课时时长数据删除');
        }else{
            $response = array('success'=>false,'message'=>'课时时长数据查询失败');
        }
        return $response;    
    }

    protected function deleteLessonReplays($course)
    {
        $lessonReplayCount = $this->getCourseLessonReplayDao()->findLessonReplaysCountByCourseId($course['id']);
        if($lessonReplayCount>0){
             $LessonReplays = $this->getCourseLessonReplayDao()->searchCourseLessonReplays(array('courseId'=>$course['id']),array('createdTime' ,'desc'),0,1000);
            foreach ($LessonReplays as  $LessonReplay) {
                $this->getCourseLessonReplayDao()->deleteCourseLessonReplay($LessonReplay['id']); 
                $LessonReplayLog = "删除课程《{$course['title']}》(#{$course['id']})的录播　{$LessonReplay['title']}";
                $this->getLogService()->info('LessonReplay', 'delete', $LessonReplayLog);
            }
            $response = array('success'=>true,'message'=>'课时录播数据删除');
        }else{
            $response = array('success'=>false,'message'=>'课时录播数据查询成功');
        }

        return $response;
    }

    protected function deleteLessonViews($course)
    {
        $lessonViewCount = $this->getLessonViewDao()->findLessonsViewsCountByCourseId($course['id']);
        if($lessonViewCount>0){
            $lessonViews = $this->getLessonViewDao()->searchLessonView(array('courseId'=>$course['id']),array('createdTime' ,'desc'),0,1000);
            foreach ($lessonViews as $lessonView) {
                $this->getLessonViewDao()->deleteLessonView($lessonView['id']);
            }
            $response = array('success'=>true,'message'=>'课时播放时长数据删除');
        }else{
            $response = array('success'=>false,'message'=>'课时播放时长数据查询失败');
        }
        return $response;
    }

    protected function deleteHomework($course)
    {
        $code = 'Homework';
        $homework = $this->getAppService()->findInstallApp($code);
        $isDeleteHomework = $homework && version_compare($homework['version'], "1.0.4", ">=");
        if($isDeleteHomework){
            $HomeworkCount = $this->getHomeworkDao()->findHomeworksCountByCourseId($course['id']);
            if($HomeworkCount>0){
                $homeworks = $this->getHomeworkDao()->searchHomeworks(array('courseId'=>$course['id']),array('createdTime' ,'desc'),0,1000);
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
                $response = array('success'=>true,'message'=>'课时作业数据删除');  
            }else{
                $response = array('success'=>false,'message'=>'课时播作业数据查询失败');
            }
        }else{
            $response = array('success'=>false,'message'=>'作业插件未安装');
        }
        return $response;
    }

    protected function deleteExercise($course)
    {
        $code = 'Homework';
        $homework = $this->getAppService()->findInstallApp($code);
        $isDeleteHomework = $homework && version_compare($homework['version'], "1.0.4", ">=");
        if($isDeleteHomework){
            $exerciseCount = $this->getExerciseDao()->findExercisesCountByCourseId($course['id']);
            if($exerciseCount>0){
                $exercises = $this->getExerciseDao()->searchExercises(array('courseId'=>$course['id']),array('createdTime' ,'desc'),0,1000);
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
                $response = array('success'=>true,'message'=>'课时练习数据删除');
            }else{
                $response = array('success'=>false,'message'=>'课时练习数据查询失败'); 
            }
        }else{
            $response = array('success'=>false,'message'=>'作业插件未安装');
        }   
        
        return $response;
    }

    protected function deleteFavorites($course)
    {
        $favoriteCount = $this->getFavoriteDao()->findFavoritesCountByCourseId($course['id']);
        if($favoriteCount>0){
            $favorites = $this->getFavoriteDao()->searchCourseFavorites(array('courseId'=>$course['id']),array('createdTime' ,'desc'),0,1000);
            foreach ($favorites as $favorite) {
                $this->getFavoriteDao()->deleteFavorite($favorite['id']);
            }
            $favoriteLog = "删除课程《{$course['title']}》(#{$course['id']})的课程收藏";
            $this->getLogService()->info('favorite', 'delete', $favoriteLog);
            $response = array('success'=>true,'message'=>'课时收藏数据删除');
        }else{
            $response = array('success'=>false,'message'=>'课时收藏数据查询失败');
        }
        return $response;
    }

    protected function deleteNotes($course)
    {
        $noteCount = $this->getCourseNoteDao()->findNotesCountByCourseId($course['id']);
        if($noteCount>0){
            $notes = $this->getCourseNoteDao()->searchNotes(array('courseId'=>$course['id']),array('createdTime' ,'desc'),0,1000);
            foreach ($notes as $note) {
                $this->getCourseNoteLikeDao()->deleteNoteLikesByNoteId($note['id']);
                $this->getCourseNoteDao()->deleteNote($note['id']);
            }
            $noteLog = "删除课程《{$course['title']}》(#{$course['id']})的课程笔记";
            $this->getLogService()->info('note', 'delete', $noteLog);
            $response = array('success'=>true,'message'=>'课时笔记数据删除');
        }else{
            $response = array('success'=>false,'message'=>'课时笔记数据查询失败');
        }
        return $response;
    }

    protected function deleteThreads($course)
    {
        $threadCount = $this->getThreadDao()->searchThreadCount(array('courseId'=>$course['id']));
        if($threadCount>0){
            $threads = $this->getThreadDao()->searchThreads(array('courseId'=>$course['id']),array('createdTime' ,'desc'),0,1000);
            foreach ($threads as $thread) {
                $this->getThreadPostDao()->deletePostsByThreadId($thread['id']);
                $this->getThreadDao()->deleteThread($thread['id']); 
            }
            $threadLog = "删除课程《{$course['title']}》(#{$course['id']})的话题";
            $this->getLogService()->info('thread', 'delete', $threadLog);   
            $response = array('success'=>true,'message'=>'课程话题数据删除');
        }else{
            $response = array('success'=>false,'message'=>'课程话题数据查询失败');
        }
        return $response;
       
    }

    protected function deleteReviews($course)
    {
        $reviewCount = $this->getReviewDao()->searchReviewsCount(array('courseId'=>$course['id']));
        if($reviewCount>0){
            $reviews = $this->getReviewDao()->searchReviews(array('courseId'=>$course['id']),array('createdTime' ,'desc'),0,1000);
            foreach ($reviews as $review) {
                $this->getReviewDao()->deleteReview($review['id']);
            }
            $reviewLog = "删除课程《{$course['title']}》(#{$course['id']})的评价";
            $this->getLogService()->info('review', 'delete', $reviewLog);
            $response = array('success'=>true,'message'=>'课程评价删除');
        }else{
            $response = array('success'=>false,'message'=>'课程评价查询失败');
        }

        return $response;
    }

    protected function deleteAnnouncements($course)
    {
        $announcementCount = $this->getAnnouncementDao()->searchAnnouncementsCount(array('targetId'=>$course['id'],'targetType'=>'course'));
        if($announcementCount>0){
            $announcements = $this->getAnnouncementDao()->searchAnnouncements(array('targetType'=>'course','targetId'=>$course['id'],array('createdTime' ,'desc'),0,1000));
            foreach ($announcements as $announcement) {
                $this->getAnnouncementDao()->deleteAnnouncement($announcement['id']);
            }
            $announcementLog = "删除课程《{$course['title']}》(#{$course['id']})的公告";
            $this->getLogService()->info('announcement', 'delete', $announcementLog);    
            $response = array('success'=>true,'message'=>'课程公告删除');
        }else{
            $response = array('success'=>false,'message'=>'课程公告查询失败');
        }

        return $response;
        
    }

    protected function deleteStatuses($course)
    {
        $statusCount = $this->getStatusDao()->searchStatusesCount(array('courseId'=>$course['id']));
        if($statusCount>0){
            $statuses = $this->getStatusDao()->searchStatuses(array('courseId'=>$course['id']),array('createdTime' ,'desc'),0,1000);
            foreach ($statuses as $status) {
               $this->getStatusDao()->deleteStatus($status['id']);
            }
            $statusLog = "删除课程《{$course['title']}》(#{$course['id']})的动态";
            $this->getLogService()->info('status', 'delete', $statusLog);
            $response = array('success'=>true,'message'=>'课程动态删除');
        }else{
            $response = array('success'=>false,'message'=>'课程动态查询失败');
        }

        return $response;
        
    }

    protected function deleteMembers($course)
    {
        $memberCount = $this->getCourseMemberDao()->searchMemberCount(array('courseId'=>$course['id']));
        if($memberCount>0){
            $members = $this->getCourseMemberDao()->searchMembers(array('courseId'=>$course['id']),array('createdTime' ,'desc'),0,1000);
            foreach ($members as $member) {
                $this->getCourseMemberDao()->deleteMember($member['id']);
            }
            $memberLog = "删除课程《{$course['title']}》(#{$course['id']})的成员";
            $this->getLogService()->info('member', 'delete', $memberLog);
            $response = array('success'=>true,'message'=>'课程成员删除');
        }else{
            $response = array('success'=>false,'message'=>'课程成员查询失败');
        }

        return $response;
        
    }

    protected function deleteCourses($course)
    {
        $this->getCourseDao()->deleteCourse($course['id']);
        $courseLog = "删除课程《{$course['title']}》(#{$course['id']})";
        $this->getLogService()->info('course', 'delete', $courseLog);
        return array('success'=>true,'message'=>'课程数据删除');
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
        return $this->createDao('Course.CourseDraftDao');
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