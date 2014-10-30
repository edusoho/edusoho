<?php
namespace Topxia\Service\Course\Tests;

use Topxia\Service\Common\BaseTestCase;

class ExerciseServiceTest extends BaseTestCase
{   

    public function testGetExercise()
    {

        $course = $this->generateCourse();

        $lesson = $this->generateLesson($course);

        $target = 'course-'.$course['id'];
        $this->generateChoiceQuestions($target, 5, 'simple');
        $this->generateChoiceQuestions($target, 5, 'normal');
        $this->generateChoiceQuestions($target, 5, 'difficulty');

        $this->generateFillQuestions($target, 5, 'simple');
        $this->generateFillQuestions($target, 5, 'normal');
        $this->generateFillQuestions($target, 5, 'difficulty');

        $this->generateDetermineQuestions($target, 5, 'simple');
        $this->generateDetermineQuestions($target, 5, 'normal');

        $this->generateEssayQuestions($target, 1, 'simple');
        $this->generateEssayQuestions($target, 2, 'normal');
        $this->generateEssayQuestions($target, 1, 'difficulty');

        $fields = array(
            'courseId' => $course['id'],
            'lessonId' => $lesson['id'],
            'questionCount' => 5,
            'difficulty' => 'simple',
            'ranges' => array("choice","single_choice","uncertain_choice","fill","determine","essay","material"),
            'source' => 'course'
        );
        $exercise = $this->getExerciseService()->createExercise($fields);
        $foundExercise = $this->getExerciseService()->getExercise($exercise[0]['id']);

        $this->assertEquals($exercise[0]['id'], $foundExercise['id']);
        $this->assertTrue(is_array($exercise[1]));
    }

    public function testGetExerciseWithNotExistExercise()
    {
        $result = $this->getExerciseService()->getExercise(99999);
        $this->assertEquals(false, $result);
    }

    public function testGetExerciseByCourseIdAndLessonId()
    {
        $course = $this->generateCourse();
        $lesson = $this->generateLesson($course);

        $target = 'course-'.$course['id'];
        $this->generateChoiceQuestions($target, 5, 'simple');

        $fields = array(
            'courseId' => $course['id'],
            'lessonId' => $lesson['id'],
            'questionCount' => 5,
            'difficulty' => 'simple',
            'ranges' => array("choice","single_choice","uncertain_choice","fill","determine","essay","material"),
            'source' => 'course'
        );
        $exercise = $this->getExerciseService()->createExercise($fields);

        $foundExercise = $this->getExerciseService()->getExercise($course['id'], $lesson['id']);
        $this->assertEquals($exercise[0]['id'], $foundExercise['id']);
    }

    public function testCreateExercise()
    {
        $course = $this->generateCourse();
        $lesson = $this->generateLesson($course);

        $target = 'course-'.$course['id'];
        $this->generateChoiceQuestions($target, 5, 'simple');

        $fields = array(
            'courseId' => $course['id'],
            'lessonId' => $lesson['id'],
            'questionCount' => 5,
            'difficulty' => 'simple',
            'ranges' => array("choice","single_choice","uncertain_choice","fill","determine","essay","material"),
            'source' => 'course'
        );

        $exercise = $this->getExerciseService()->createExercise($fields);
        $this->assertGreaterThan(0, $exercise[0]['id']);
        $this->assertEquals($fields['courseId'], $exercise[0]['courseId']);
        $this->assertEquals($fields['lessonId'], $exercise[0]['lessonId']);
        $this->assertEquals($fields['questionCount'], $exercise[0]['itemCount']);
        $this->assertCount(7, json_decode($exercise[0]['questionTypeRange']));
        $this->assertCount($fields['questionCount'], $exercise[1]);
        $this->assertEquals($fields['source'], $exercise[0]['source']);
    }

    public function testUpdateExercise()
    {
        $course = $this->generateCourse();
        $lesson = $this->generateLesson($course);

        $target = 'course-'.$course['id'];
        $this->generateChoiceQuestions($target, 5, 'simple');

        $fields = array(
            'courseId' => $course['id'],
            'lessonId' => $lesson['id'],
            'questionCount' => 5,
            'difficulty' => 'simple',
            'ranges' => array("choice","single_choice","uncertain_choice","fill","determine","essay","material"),
            'source' => 'course'
        );

        $exercise = $this->getExerciseService()->createExercise($fields);

        $updatedFields = array(
            'courseId' => $course['id'],
            'lessonId' => $lesson['id'],
            'questionCount' => 3,
            'difficulty' => 'simple',
            'ranges' => array('choice'),
            'source' => 'course'
        );

        $updatedExercise = $this->getExerciseService()->updateExercise($exercise[0]['id'], $updatedFields);
        $this->assertEquals($updatedFields['courseId'], $updatedExercise[0]['courseId']);
        $this->assertEquals($updatedFields['lessonId'], $updatedExercise[0]['lessonId']);
        $this->assertEquals($updatedFields['questionCount'], $updatedExercise[0]['itemCount']);
        $this->assertEquals($updatedFields['difficulty'], $updatedExercise[0]['difficulty']);
        $this->assertEquals($updatedFields['source'], $updatedExercise[0]['source']);
        $this->assertCount($updatedFields['questionCount'], $updatedExercise[1]);
        $this->assertCount(1, json_decode($updatedExercise[0]['questionTypeRange']));
    }

    
    public function testDeleteExercise()
    {
        $course = $this->generateCourse();
        $lesson = $this->generateLesson($course);

        $target = 'course-'.$course['id'];
        $this->generateChoiceQuestions($target, 5, 'simple');

        $fields = array(
            'courseId' => $course['id'],
            'lessonId' => $lesson['id'],
            'questionCount' => 5,
            'difficulty' => 'simple',
            'ranges' => array("choice","single_choice","uncertain_choice","fill","determine","essay","material"),
            'source' => 'course'
        );

        $exercise = $this->getExerciseService()->createExercise($fields);

        $result = $this->getExerciseService()->deleteExercise($exercise[0]['id']);
        $this->assertNotNull($result);
    }

    /**
    * @expectedException Topxia\Service\Common\ServiceException
    */

    public function testDeleteExerciseWithNotExist()
    {
        $this->getExerciseService()->deleteExercise(999);
    }

    public function testfindExercisesByCourseIdAndLessonIds()
    {
        $course = $this->generateCourse();
        $lesson1 = $this->generateLesson($course);
        $lesson2 = $this->generateLesson($course);

        $target = 'course-'.$course['id'];
        $this->generateChoiceQuestions($target, 5, 'simple');

        $fields1 = array(
            'courseId' => $course['id'],
            'lessonId' => $lesson1['id'],
            'questionCount' => 5,
            'difficulty' => 'simple',
            'ranges' => array("choice","single_choice","uncertain_choice","fill","determine","essay","material"),
            'source' => 'course'
        );

        $fields2 = array(
            'courseId' => $course['id'],
            'lessonId' => $lesson2['id'],
            'questionCount' => 5,
            'difficulty' => 'simple',
            'ranges' => array("choice","single_choice","uncertain_choice","fill","determine","essay","material"),
            'source' => 'course'
        );

        $exercise1 = $this->getExerciseService()->createExercise($fields1);
        $exercise2 = $this->getExerciseService()->createExercise($fields2);
        $exerciseIds = array(
            $exercise1[0]['lessonId'],
            $exercise2[0]['lessonId']
        );

        $exercises = $this->getExerciseService()->findExercisesByCourseIdAndLessonIds($course['id'], $exerciseIds);
        $this->assertContains($exercise1[0], $exercises);
        $this->assertContains($exercise2[0], $exercises);
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

    private function generateCourse()
    {
        return $this->getCourseService()->createCourse(array(
            'title' => 'test course'
        ));
    }

    private function generateLesson($course)
    {
        return $this->getCourseService()->createLesson(array(
            'courseId' => $course['id'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
            'type' => 'text'
        ));
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

    private function getExerciseService()
    {
        return $this->getServiceKernel()->createService('Course.ExerciseService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    private function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question.QuestionService');
    }

}