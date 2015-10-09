<?php
namespace Topxia\Service\Course\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Course\CourseService;
use Topxia\Service\User\UserService;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\User\CurrentUser;
use Topxia\Service\Taxonomy\TagService;

class CourseDeleteServiceTest extends BaseTestCase
{
	public function testdelete()
    {
    	$course = array(
            'title' => 'online test course '
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $result = $this->getCourseService()->getCoursesCount();
        $this->assertEquals(1,$result);

        $status = array('courseId'=>$createCourse['id']);
    	$this->getStatusService()->publishStatus($status);
    	$count = $this->getStatusService()->searchStatusesCount(array('courseId'=>$createCourse['id']));
    	$this->assertEquals(1,$count);

    	$announcementInfo = array(
        	'targetType' => 'course',
        	'targetId' => $createCourse['id'],
        	'content' => 'test_announcement',
            'startTime'=>time(),
            'endTime'=>time()+3600*1000,
            'url'=> 'http://www.baidu.com'
        );
        $createdAnnouncement = $this->getAnnouncementService()->createAnnouncement($announcementInfo);

        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $reviewInfo = array(
            'title'=>'title',
            'content'=>'content',
            'rating'=>$createCourse['rating'],
            'userId'=>$registeredUser['id'],
            'courseId'=>$createCourse['id']
            );
        $savedReview = $this->getReviewService()->saveReview($reviewInfo);

        $thread = array(
            'courseId' => $createCourse['id'],
			'type' => 'discussion',
			'title' => 'test thread',
			'content' => 'test content',
		);
    	$createdThread = $this->getThreadService()->createThread($thread);

    	$lesson = $this->getCourseService()->createLesson(array(
            'courseId' => $createCourse['id'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
            'type' => 'text'
        ));

        $note = $this->getNoteService()->saveNote(array(
            'content'=>'note content',
            'lessonId'=>$lesson['id'],
            'courseId'=>$createCourse['id']
        ));

		$lessonView = array('title'=>'lessonView','courseId'=>$createCourse['id'],'lessonId'=>$lesson['id']);
        $this->getCourseService()->createLessonView($lessonView);

        $courseLessonReplay = array('courseId'=>$createCourse['id'],'title'=>'录播回放');
        $courseLessonReplay = $this->getCourseService()->addCourseLessonReplay($courseLessonReplay);
    
        $draft = array(
            'userId' => 1,
            'title' => 'title',
            'courseId' => $createCourse['id'],
            'lessonId' => $lesson['id']
        );
        $this->getCourseService()->createCourseDraft($draft);

        $chapter = array('courseId' => $createCourse['id'], 'title' => 'chapter 1', 'type' => 'chapter','number' =>'1','seq'=>'1');
        $createdChapter = $this->getCourseService()->createChapter($chapter);

        $target ='course-'.$createCourse['id'];
        $testpaper = array('name' => 'Test','target'=>$target);
        $testpaper = $this->getTestpaperService()->addTestpaper($testpaper);
        $this->assertEquals('Test',$testpaper['name']);

        $question = array(
            'type' => 'single_choice',
            'stem' => 'test single choice question 1.',
            'choices' => array(
                'question 1 -> choice 1',
                'question 1 -> choice 2',
                'question 1 -> choice 3',
                'question 1 -> choice 4',
            ),
            'answer' => array(1),
            'target' =>$target
        );
        $question = $this->getQuestionService()->createQuestion($question);
        
        $types = array('questions','testpapers','materials','chapters','drafts','lessons','lessonLearns','lessonReplays','lessonViews','favorites','notes','threads','reviews','announcements','statuses','members','course');
        foreach($types as $type){
        	$this->getCourseDeleteService()->delete($createCourse['id'],$type);
        }

        $questionCount = $this->getQuestionDao()->searchQuestionsCount(array('targetPrefix' => "course-{$createCourse['id']}"));
        $this->assertEquals(0,$questionCount);
        $testpaperCount = $this->getTestpaperDao()->searchTestpapersCount(array('target'=>"course-{$createCourse['id']}"));
		$this->assertEquals(0,$testpaperCount);
		$materialCount = $this->getMaterialDao()->getMaterialCountByCourseId($createCourse['id']);
		$this->assertEquals(0,$materialCount);
		$chapterCount = $this->getCourseChapterDao()->findChaptersCountByCourseId($createCourse['id']);
		$this->assertEquals(0,$chapterCount);
		$draftCount = $this->getDraftDao()->findDraftsCountByCourseId($createCourse['id']);
		$this->assertEquals(0,$draftCount);
		$lessonCount = $this->getLessonDao()->searchLessonCount(array('courseId'=>$createCourse['id']));
		$this->assertEquals(0,$lessonCount);
		$lessonLearnCount = $this->getLessonLearnDao()->searchLearnCount(array('courseId'=>$createCourse['id']));
		$this->assertEquals(0,$lessonLearnCount);
		$lessonReplayCount = $this->getCourseLessonReplayDao()->findLessonReplaysCountByCourseId($createCourse['id']);
		$this->assertEquals(0,$lessonReplayCount);
		$lessonViewCount = $this->getLessonViewDao()->findLessonsViewsCountByCourseId($createCourse['id']);
		$this->assertEquals(0,$lessonViewCount);
		$favoriteCount = $this->getFavoriteDao()->findFavoritesCountByCourseId($createCourse['id']);
		$this->assertEquals(0,$favoriteCount);
		$noteCount = $this->getCourseNoteDao()->findNotesCountByCourseId($createCourse['id']);
		$this->assertEquals(0,$noteCount);
		$threadCount = $this->getThreadDao()->searchThreadCount(array('courseId'=>$createCourse['id']));
		$this->assertEquals(0,$threadCount);
		$reviewCount = $this->getReviewDao()->searchReviewsCount(array('courseId'=>$createCourse['id']));
		$this->assertEquals(0,$reviewCount);
		$announcementCount = $this->getAnnouncementDao()->searchAnnouncementsCount(array('targetId'=>$createCourse['id'],'targetType'=>'course'));
		$this->assertEquals(0,$announcementCount);
		$statusCount = $this->getStatusDao()->searchStatusesCount(array('courseId'=>$createCourse['id']));
		$this->assertEquals(0,$statusCount);
		$memberCount = $this->getCourseMemberDao()->searchMemberCount(array('courseId'=>$createCourse['id']));
		$this->assertEquals(0,$memberCount);
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
    protected function getStatusService()
    {
        return $this->getServiceKernel()->createService('User.StatusService');
    }
    protected function getAnnouncementService()
    {
        return $this->getServiceKernel()->createService('Announcement.AnnouncementService');
    }
    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
    protected function getReviewService()
    {
        return $this->getServiceKernel()->createService('Course.ReviewService');
    }
    protected function getThreadService()
    {
    	return $this->getServiceKernel()->createService('Course.ThreadService');
    }
    protected function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question.QuestionService');
    }
    protected function getTestpaperService()
    {
        return $this->getServiceKernel()->createService('Testpaper.TestpaperService');
    }
    protected function getCourseDeleteService()
    {
        return $this->getServiceKernel()->createService('Course.CourseDeleteService');
    }
    protected function getNoteService()
    {
        return $this->getServiceKernel()->createService('Course.NoteService');
    }

    protected function getQuestionDao()
    {
        return $this->getServiceKernel()->createDao('Question.QuestionDao');
    }

    protected function getQuestionFavoriteDao()
    {
        return $this->getServiceKernel()->createDao('Question.QuestionFavoriteDao');
    }

    protected function getTestpaperResultDao()
    {
        return $this->getServiceKernel()->createDao('Testpaper.TestpaperResultDao');
    }

    protected function getTestpaperItemResultDao(){
        
        return $this->getServiceKernel()->createDao('Testpaper.TestpaperItemResultDao');
    }

    protected function getTestpaperItemDao(){
        return $this->getServiceKernel()->createDao('Testpaper.TestpaperItemDao');
    }

    protected function getTestpaperDao()
    {
        return $this->getServiceKernel()->createDao('Testpaper.TestpaperDao');
    }

    protected function getCourseDao()
    {
        return $this->getServiceKernel()->createDao('Course.CourseDao');
    }
    protected function getFavoriteDao()
    {
        return $this->getServiceKernel()->createDao('Course.FavoriteDao');
    }

    protected function getCourseNoteDao()
    {
        return $this->getServiceKernel()->createDao('Course.CourseNoteDao');
    }

    protected function getCourseNoteLikeDao()
    {
        return $this->getServiceKernel()->createDao('Course.CourseNoteLikeDao');
    }

    protected function getThreadDao()
    {
        return $this->getServiceKernel()->createDao('Course.ThreadDao');
    }

    protected function getThreadPostDao()
    {
        return $this->getServiceKernel()->createDao('Course.ThreadPostDao');
    }

    protected function getReviewDao()
    {
        return $this->getServiceKernel()->createDao('Course.ReviewDao');
    }

    protected function getAnnouncementDao()
    {
        return $this->getServiceKernel()->createDao('Announcement.AnnouncementDao');
    }

    protected function getStatusDao()
    {
        return $this->getServiceKernel()->createDao('User.StatusDao');
    }

    protected function getCourseMemberDao()
    {
        return $this->getServiceKernel()->createDao('Course.CourseMemberDao');
    }

    protected function getMaterialDao()
    {
        return $this->getServiceKernel()->createDao('Course.CourseMaterialDao');
    }
    protected function getCourseChapterDao()
    {
        return $this->getServiceKernel()->createDao('Course.CourseChapterDao');
    }

    protected function getDraftDao ()
    {
        return $this->getServiceKernel()->createDao('Course.CourseDraftDao');
    }
    protected function getLessonDao()
    {
        return $this->getServiceKernel()->createDao('Course.LessonDao');
    }
    protected function getLessonLearnDao ()
    {
        return $this->getServiceKernel()->createDao('Course.LessonLearnDao');
    }
     protected function getCourseLessonReplayDao ()
    {
        return $this->getServiceKernel()->createDao('Course.CourseLessonReplayDao');
    }

    protected function getLessonViewDao ()
    {
        return $this->getServiceKernel()->createDao('Course.LessonViewDao');
    }

}