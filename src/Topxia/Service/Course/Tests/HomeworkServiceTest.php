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
        $lesson = $this->generateCourseLesson();
        $courseId = $lesson['courseId'];
        $lessonId = $lesson['id'];

        $target = 'course-'.$courseId;

        $choiceQuestions = $this->generateChoiceQuestions($target, 5, 'simple');
        $choiceQuestionIds = $this->getQuestionIds($choiceQuestions);

        $fillQuestion = $this->generateFillQuestions($target, 1, 'difficulty');
        $fillQuestionId = $this->getQuestionIds($fillQuestion);

        $essayQuestion = $this->generateEssayQuestions($target, 1, 'difficulty');
        $essayQuestionId = $this->getQuestionIds($essayQuestion);

        $excludeIds = array(
            $choiceQuestionIds,
            $fillQuestionId,
            $essayQuestionId
        );
        $excludeIds = $this->transferEcludeIdsFormat($excludeIds);

        $fields = array(
            'description' => 'it is a test homework',
            'completeLimit' => 'inherited',
            'itemCount' => $this->getItemsCount($excludeIds),
            'excludeIds'=>$excludeIds
        );

        $homework = $this->getHomeworkService()->createHomework($courseId,$lessonId,$fields);

        $this->assertNotNull($homework);
        $this->assertEquals($fields['description'],$homework['description']);
        $this->assertEquals($fields['itemCount'],$homework['itemCount']);
        $this->assertEquals($fields['completeLimit'],$homework['completeLimit']);
    }

    public function testUnGetHomework()
    {
        $homework = $this->getHomeworkService()->getHomework(-1);
        $this->assertNull($homework);
    }
    public function testGetHomework($value='')
    {
        $generateHomework = $this->generateHomework();
        $homework = $this->getHomeworkService()->getHomework($generateHomework['id']);
        $this->assertEquals($generateHomework['id'],$homework['id']);
    }

    public function testUpdateHomework($value='')
    {
        $generateHomework = $this->generateHomework();
        $target = 'course-'.$generateHomework['courseId'];

        $essayQuestion = $this->generateEssayQuestions($target, 2, 'difficulty');
        $essayQuestionId = $this->getQuestionIds($essayQuestion);

        $excludeIds = array(
            $essayQuestionId
        );
        $excludeIds = $this->transferEcludeIdsFormat($excludeIds);

        $fields = array(
            'description' => 'it is a another test homework',
            'completeLimit' => 'yes',
            'itemCount' => $this->getItemsCount($excludeIds),
            'excludeIds'=>$excludeIds
        );

        $homework = $this->getHomeworkService()->updateHomework($generateHomework['id'], $fields);

        $this->assertTrue(is_array($homework));
        $this->assertEquals($generateHomework['id'], $homework['id']);
        $this->assertEquals($fields['description'], $homework['description']);
        $this->assertEquals($fields['completeLimit'], $homework['completeLimit']);
        $this->assertEquals($fields['itemCount'], $homework['itemCount']);
    }

    public function testRemoveHomework($value='')
    {
        $generateHomework = $this->generateHomework();
        $result = $this->getHomeworkService()->removeHomework($generateHomework['id']);
        $this->assertTrue($result);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testRemoveHomeworkException($value='')
    {
        $this->getHomeworkService()->removeHomework(-1);
    }

    public function testfindHomeworksByCourseIdAndLessonId($value='')
    {
        $generatedHomework = $this->generateHomework();
        $homework = $this->getHomeworkService()->findHomeworksByCourseIdAndLessonId($generatedHomework['courseId'], $generateHomework['lessonId']);
        $this->assertEquals($generatedHomework['id'], $homework['id']);
    }

    public function testFindHomeworksByCourseIdAndLessonIds($value='')
    {   
        $course = $this->generateCourse2();
        $lesson1 = $this->generateLesson2($course); 
        $lesson2 = $this->generateLesson2($course); 
        $lesson3 = $this->generateLesson2($course); 

        $generatedHomework1 = $this->generateHomework2($course, $lesson1);
        $generatedHomework2 = $this->generateHomework2($course, $lesson2);
        $generatedHomework3 = $this->generateHomework2($course, $lesson3);

        $courseId = $course['id'];
        $lessonIds = array($generatedHomework1['lessonId'], $generatedHomework2['lessonId'], $generatedHomework3['lessonId']);
        $homeworks = $this->getHomeworkService()->findHomeworksByCourseIdAndLessonIds($courseId, $lessonIds);

        $this->assertContains($generatedHomework1, $homeworks);
        $this->assertContains($generatedHomework2, $homeworks);
        $this->assertContains($generatedHomework3, $homeworks);
    }

    /**
     * @group current
     */
    public function testFindHomeworksByCreatedUserId()
    {
        $generatedHomework1 = $this->generateHomework();
        $generatedHomework2 = $this->generateHomework();
        $generatedHomework3 = $this->generateHomework();

        $user = $this->getCurrentUser();

        $homeworks = $this->getHomeworkService()->findHomeworksByCreatedUserId($user['id']);

        $this->assertContains($generatedHomework1, $homeworks);
        $this->assertContains($generatedHomework2, $homeworks);
        $this->assertContains($generatedHomework3, $homeworks);
    }

    public function testGetHomeworkResult();

    private function generateCourse2()
    {
        return $this->getCourseService()->createCourse(array(
            'title' => 'test course'
        ));
    }

    private function generateLesson2($course)
    {
        return $this->getCourseService()->createLesson(array(
            'courseId' => $course['id'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
            'type' => 'text'
        ));
    }

    private function generateHomework2($course, $lesson)
    {
        $courseId = $course['id'];
        $lessonId = $lesson['id'];

        $target = 'course-'.$courseId;

        $choiceQuestions = $this->generateChoiceQuestions($target, 5, 'simple');
        $choiceQuestionIds = $this->getQuestionIds($choiceQuestions);

        $fillQuestion = $this->generateFillQuestions($target, 1, 'difficulty');
        $fillQuestionId = $this->getQuestionIds($fillQuestion);

        $essayQuestion = $this->generateEssayQuestions($target, 1, 'difficulty');
        $essayQuestionId = $this->getQuestionIds($essayQuestion);

        $excludeIds = array(
            $choiceQuestionIds,
            $fillQuestionId,
            $essayQuestionId
        );
        $excludeIds = $this->transferEcludeIdsFormat($excludeIds);

        $fields = array(
            'description' => 'it is a test homework',
            'completeLimit' => 'inherited',
            'itemCount' => $this->getItemsCount($excludeIds),
            'excludeIds'=>$excludeIds
        );

        $homework = $this->getHomeworkService()->createHomework($courseId,$lessonId,$fields);

        $this->assertNotNull($homework);
        return $homework;
    }

    private function generateHomework($value='')
    {
        $lesson = $this->generateCourseLesson();
        $courseId = $lesson['courseId'];
        $lessonId = $lesson['id'];

        $target = 'course-'.$courseId;

        $choiceQuestions = $this->generateChoiceQuestions($target, 5, 'simple');
        $choiceQuestionIds = $this->getQuestionIds($choiceQuestions);

        $fillQuestion = $this->generateFillQuestions($target, 1, 'difficulty');
        $fillQuestionId = $this->getQuestionIds($fillQuestion);

        $essayQuestion = $this->generateEssayQuestions($target, 1, 'difficulty');
        $essayQuestionId = $this->getQuestionIds($essayQuestion);

        $excludeIds = array(
            $choiceQuestionIds,
            $fillQuestionId,
            $essayQuestionId
        );
        $excludeIds = $this->transferEcludeIdsFormat($excludeIds);

        $fields = array(
            'description' => 'it is a test homework',
            'completeLimit' => 'inherited',
            'itemCount' => $this->getItemsCount($excludeIds),
            'excludeIds'=>$excludeIds
        );

        $homework = $this->getHomeworkService()->createHomework($courseId,$lessonId,$fields);

        $this->assertNotNull($homework);
        return $homework;
    }

    private function generateRegisteruser()
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

    private function generateCourse()
    {
        $course = array(
            'title' => 'online test course 1',
        );
        $createdCourse = $this->getCourseService()->createCourse($course);

        $this->assertGreaterThan(0, $createdCourse['id']);
        $this->assertEquals($course['title'], $createdCourse['title']);
        return  $createdCourse;

    }

    private function transferEcludeIdsFormat($excludeIds=array())
    {
        return implode(',', $excludeIds);
    }

    private function getItemsCount($items='')
    {
        return count(explode(',', $items));
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

    private function getQuestionIds($questions = array())
    {
        $questionsIds = array();

        if (count($questions) > 1) {
            foreach ($questions as $key => $question) {
                $questionsIds[] = $question['id'];
            }

            $questionsIds = implode(',', $questionsIds);
        } else {
            $questionsIds = $questions[0]['id'];
        }

        return $questionsIds;
    }

    private function generateCourseLesson()
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

    private function generateChoiceQuestions($target, $count, $difficulty = null)
    {
        $questions = array();
        for ($i=0; $i<$count; $i++) {
            $question = array(
                'type' => 'choice',
                'stem' => 'test single choice question.',
                'choices' => array(
                    'question -> choice 1',
                    'question -> choice 2',
                    'question -> choice 3',
                    'question -> choice 4',
                ),
                'answer' => array(1, 2),
                'target' => $target,
                'difficulty' => empty($difficulty) ? 'normal' : $difficulty,
            );

            $questions[] = $this->getQuestionService()->createQuestion($question);
        }
        return $questions;
    }

    private function generateFillQuestions($target, $count, $difficulty = null)
    {
        $questions = array();
        for ($i=0; $i<$count; $i++) {
            $question = array(
                'type' => 'fill',
                'stem' => 'fill question [[aaa]].',
                'target' => $target,
                'difficulty' => empty($difficulty) ? 'normal' : $difficulty,
            );

            $questions[] = $this->getQuestionService()->createQuestion($question);
        }
        return $questions;
    }

    private function generateDetermineQuestions($target, $count, $difficulty = null)
    {
        $questions = array();
        for ($i=0; $i<$count; $i++) {
            $question = array(
                'type' => 'determine',
                'stem' => 'determine question.',
                'target' => $target,
                'answer' => array(0),
                'difficulty' => empty($difficulty) ? 'normal' : $difficulty,
            );

            $questions[] = $this->getQuestionService()->createQuestion($question);
        }
        return $questions;
    }

    private function generateEssayQuestions($target, $count, $difficulty = null)
    {
        $questions = array();
        for ($i=0; $i<$count; $i++) {
            $question = array(
                'type' => 'essay',
                'stem' => 'essay question.',
                'target' => $target,
                'answer' => array('xxx'),
                'difficulty' => empty($difficulty) ? 'normal' : $difficulty,
            );

            $questions[] = $this->getQuestionService()->createQuestion($question);
        }
        return $questions;
    }

    private function generateMaterialQuestions($target, $count, $difficulty = null)
    {
        $questions = array();
        for ($i=0; $i<$count; $i++) {
            $question = array(
                'type' => 'material',
                'stem' => 'material question.',
                'target' => $target,
                'difficulty' => empty($difficulty) ? 'normal' : $difficulty,
            );

            $questions[] = $this->getQuestionService()->createQuestion($question);
        }
        return $questions;
    }

}