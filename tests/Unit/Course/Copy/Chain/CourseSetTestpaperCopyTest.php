<?php

namespace Tests\Unit\Course\Copy\Chain;

use Biz\Activity\Dao\ActivityDao;
use Biz\Activity\Service\TestpaperActivityService;
use Biz\BaseTestCase;
use Biz\Course\Copy\Chain\CourseSetTestpaperCopy;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Question\Service\QuestionService;
use Biz\Testpaper\Service\TestpaperService;

class CourseSetTestpaperCopyTest extends BaseTestCase
{
    public function testCopyEntity()
    {
        $courseSet = $this->createNewCourseSet();
        $course = $this->createNewCourse($courseSet['id']);

        $testpaper = $this->createTestpaper();

        $fields = array(
            'mediaId' => $testpaper['id'],
            'doTimes' => 0,
            'redoInterval' => 0,
            'limitedTime' => 0,
            'checkType' => 'score',
            'finishCondition' => array(
                'type' => 'score',
                'finishScore' => 5,
            ),
            'testMode' => 'normal',
        );
        $activity = $this->getTestpaperActivityService()->createActivity($fields);

        $testpaperActivity = array(
            'title' => 'test activity',
            'mediaType' => 'testpaper',
            'mediaId' => $activity['id'],
            'fromCourseId' => $course['id'],
            'fromCourseSetId' => $courseSet['id'],
        );
        $this->getActivityDao()->create($testpaperActivity);

        $copiedCourseSet = $courseSet = $this->createNewCourseSet();
        $copiedCourse = $this->createNewCourse($copiedCourseSet['id']);
        $copiedCourse['newCourseSet'] = $copiedCourseSet;
        $copiedCourse['isCopy'] = true;

        $copy = new CourseSetTestpaperCopy($this->biz, array());
        $newTestpapers = $copy->copy($course, $copiedCourse, true);
        $newTestpaper = reset($newTestpapers);
        $this->assertEquals($testpaper['name'], $newTestpaper['name']);
    }

    public function testCopyEntityWithNoTestpaper()
    {
        $courseSet = $this->createNewCourseSet();
        $course = $this->createNewCourse($courseSet['id']);

        $copiedCourseSet = $courseSet = $this->createNewCourseSet();
        $copiedCourse = $this->createNewCourse($copiedCourseSet['id']);
        $copiedCourse['newCourseSet'] = $copiedCourseSet;
        $copiedCourse['isCopy'] = true;

        $copy = new CourseSetTestpaperCopy($this->biz, array());
        $result = $copy->copy($course, $copiedCourse, true);
        $this->assertEmpty($result);
    }

    protected function createTestpaper()
    {
        $this->generateChoiceQuestions(1, 2);
        $this->generateFillQuestions(1, 2);
        $this->generateDetermineQuestions(1, 2);
        $this->generateEssayQuestions(1, 2);
        $fields1 = array(
            'name' => 'testpaper',
            'description' => 'testpaper description',
            'mode' => 'range',
            'ranges' => array('courseId' => 0),
            'counts' => array('choice' => 2, 'fill' => 2, 'determine' => 1),
            'scores' => array('choice' => 2, 'fill' => 2, 'determine' => 2),
            'missScores' => array('choice' => 1, 'uncertain_choice' => 1),
            'courseSetId' => 1,
            'courseId' => 0,
            'passedScore' => 60,
            'pattern' => 'questionType',
            'type' => 'testpaper',
        );

        return $this->getTestpaperService()->buildTestpaper($fields1, 'testpaper');
    }

    protected function createNewCourse($courseSetId)
    {
        $courses = $this->getCourseService()->findCoursesByCourseSetIds(array($courseSetId));

        if (empty($courses)) {
            $courseFields = array(
                'title' => '第一个教学计划',
                'courseSetId' => 1,
                'learnMode' => 'lockMode',
                'expiryDays' => 0,
                'expiryMode' => 'forever',
            );

            $course = $this->getCourseService()->createCourse($courseFields);
        } else {
            $course = $courses[0];
        }

        $this->assertNotEmpty($course);

        return $course;
    }

    protected function generateChoiceQuestions($courseId, $count, $difficulty = null)
    {
        $questions = array();
        for ($i = 0; $i < $count; ++$i) {
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
                'courseSetId' => $courseId,
                'target' => 'course/'.$courseId,
                'difficulty' => empty($difficulty) ? 'normal' : $difficulty,
            );

            $questions[] = $this->getQuestionService()->create($question);
        }

        return $questions;
    }

    protected function generateFillQuestions($courseId, $count, $difficulty = null)
    {
        $questions = array();
        for ($i = 0; $i < $count; ++$i) {
            $question = array(
                'type' => 'fill',
                'stem' => 'fill question [[aaa]].',
                'target' => 'course/'.$courseId,
                'courseSetId' => $courseId,
                'difficulty' => empty($difficulty) ? 'normal' : $difficulty,
            );

            $questions[] = $this->getQuestionService()->create($question);
        }

        return $questions;
    }

    protected function generateDetermineQuestions($courseId, $count, $difficulty = null)
    {
        $questions = array();
        for ($i = 0; $i < $count; ++$i) {
            $question = array(
                'type' => 'determine',
                'stem' => 'determine question.',
                'target' => 'course/'.$courseId,
                'courseSetId' => $courseId,
                'answer' => array(0),
                'difficulty' => empty($difficulty) ? 'normal' : $difficulty,
            );

            $questions[] = $this->getQuestionService()->create($question);
        }

        return $questions;
    }

    protected function generateEssayQuestions($courseId, $count, $difficulty = null)
    {
        $questions = array();
        for ($i = 0; $i < $count; ++$i) {
            $question = array(
                'type' => 'essay',
                'stem' => 'essay question.',
                'target' => 'course/'.$courseId,
                'courseSetId' => $courseId,
                'answer' => array('xxx'),
                'difficulty' => empty($difficulty) ? 'normal' : $difficulty,
            );

            $questions[] = $this->getQuestionService()->create($question);
        }

        return $questions;
    }

    protected function createNewCourseSet()
    {
        $courseSetFields = array(
            'title' => '新课程开始！',
            'type' => 'normal',
        );
        $courseSet = $this->getCourseSetService()->createCourseSet($courseSetFields);

        $this->assertNotEmpty($courseSet);

        return $courseSet;
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->biz->service('Course:CourseSetService');
    }

    /**
     * @return ActivityDao
     */
    protected function getActivityDao()
    {
        return $this->biz->dao('Activity:ActivityDao');
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->biz->service('Testpaper:TestpaperService');
    }

    /**
     * @return TestpaperActivityService
     */
    protected function getTestpaperActivityService()
    {
        return $this->biz->service('Activity:TestpaperActivityService');
    }

    /**
     * @return QuestionService
     */
    protected function getQuestionService()
    {
        return $this->biz->service('Question:QuestionService');
    }
}
