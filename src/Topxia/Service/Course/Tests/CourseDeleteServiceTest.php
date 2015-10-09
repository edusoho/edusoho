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

        
        $homework = array('description'=>'ces','courseId'=>$createCourse['id']);
		$homework = $this->getHomeworkService()->addHomework($homework);
		$this->assertEquals('ces',$homework['description']);
		$homeworkItem = array('homeworkId'=>$homework['id']);
		$this->getHomeworkService()->createHomeworkItems($homework['id'],$homeworkItem);

    
		$exercise = array('source'=>'course','courseId'=>$createCourse['id']);
		$exercise = $this->getExerciseService()->addExercise($exercise);
		$this->assertEquals('course',$exercise['source']);
		
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
        
        $types = array('questions','testpapers','materials','chapters','drafts','lessons','lessonLearns','lessonReplays','lessonViews','homeworks','exercises','favorites','notes','threads','reviews','announcements','statuses','members','course');
        foreach($types as $type){
        	$this->getCourseDeleteService()->delete($createCourse['id'],$type);
        }

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
    protected function getHomeworkService()
    {
        return $this->getServiceKernel()->createService('Homework:Homework.HomeworkService');
    }
    protected function getExerciseService()
    {
        return $this->getServiceKernel()->createService('Homework:Homework.ExerciseService');
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
}