<?php 
namespace Topxia\Service\Course\Test;


use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Course\CourseService;
use Topxia\Service\User\UserService;
use Topxia\Common\ArrayToolkit;

class HomeworkServiceTest extends BaseTestCase
{

	public function testCreateHomework($value='')
	{
		$lesson = $this->createCourseLesson();
		$courseId = $lesson['courseId'];
		$lessonId = $lesson['id'];
		$question = $this->createQuestion();

		$fields = array(
			'description' => 'it is test homework',
			'completeLimit' => 'inherited',
			'itemCount' => 1,
			'excludeIds'=>$question['id'].'',
		);

		$homework = $this->getHomeworkService()->createHomework($courseId,$lessonId,$fields);
		$this->assertEquals($fields['description'],$homework['description']);
		$this->assertEquals($fields['itemCount'],$homework['itemCount']);
		$this->assertEquals($fields['completeLimit'],$homework['completeLimit']);
	}


    private function registeruser()
    {
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);

        $this->assertEquals($userInfo['nickname'], $registeredUser['nickname']);
        $this->assertEquals($userInfo['email'], $registeredUser['email']);
        $this->assertTrue($this->getUserService()->verifyPassword($registeredUser['id'], $userInfo['password']));

        /*default value Test*/
        $this->assertEquals('default', $registeredUser['type']);
        $this->assertEquals(0, $registeredUser['point']);
        $this->assertEquals(0, $registeredUser['coin']);
        $this->assertEquals(0, $registeredUser['locked']);
        $this->assertEquals(0, $registeredUser['loginTime']);
        $this->assertEquals(0, $registeredUser['emailVerified']);
        $this->assertEquals(array('ROLE_USER'), $registeredUser['roles']);
        return  $registeredUser;
    }

    private function createCourse()
    {
        $course = array(
            'title' => 'online test course 1',
        );
        $createdCourse = $this->getCourseService()->createCourse($course);

        $this->assertGreaterThan(0, $createdCourse['id']);
        $this->assertEquals($course['title'], $createdCourse['title']);
        return  $createdCourse;

    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
    
    private function getHomeworkService()
    {
        return $this->getServiceKernel()->createService('Course.HomeworkService');
    }

    private function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question.QuestionService');
    }

    private function createCourseLesson()
    {
        $course = array(
            'title' => 'online test course 1',
        );
        $course = $this->getCourseService()->createCourse($course);

        $lesson = array(
            'courseId' => $course['id'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
            'type' => 'text'
        );
        $createdLesson = $this->getCourseService()->createLesson($lesson);

        $this->assertGreaterThan(0,  $createdLesson['id']);
        $this->assertEquals($lesson['courseId'], $createdLesson['courseId']);
        $this->assertEquals($lesson['title'], $createdLesson['title']);
        $this->assertEquals($lesson['content'], $createdLesson['content']);
        $this->assertEquals(1, $createdLesson['number']);
        $this->assertEquals(1, $createdLesson['seq']);

        $lesson = array(
            'courseId' => $course['id'],
            'title' => 'test lesson 2',
            'content' => 'test lesson content 2',
            'type' => 'text'
        );
        $createdLesson = $this->getCourseService()->createLesson($lesson);

        $this->assertEquals(2, $createdLesson['number']);
        $this->assertEquals(2, $createdLesson['seq']);
        return  $createdLesson;
    }

    private function createQuestion()
    {
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
            'target' => 'course-1',
        );

        $question = $this->getQuestionService()->createQuestion($question);
        $this->assertTrue(is_array($question));
    	$this->assertEquals($question['type'],$question['type']);

    	return $question;
    }

}