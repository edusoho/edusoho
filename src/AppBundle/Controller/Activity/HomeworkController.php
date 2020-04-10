<?php

namespace AppBundle\Controller\Activity;

use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use Topxia\Service\Common\ServiceKernel;
use Biz\Activity\Service\ActivityService;
use Biz\Question\Service\QuestionService;
use Biz\Testpaper\Service\TestpaperService;
use Symfony\Component\HttpFoundation\Request;
use Biz\Activity\Service\HomeworkActivityService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;

class HomeworkController extends BaseActivityController implements ActivityActionInterface
{
    public function showAction(Request $request, $activity, $preview = 0)
    {
        if ($preview) {
            return $this->previewHomework($activity['id'], $activity['fromCourseId']);
        }

        $user = $this->getUser();

        $activity = $this->getActivityService()->getActivity($activity['id']);
        $homework = $this->getTestpaperService()->getTestpaperByIdAndType($activity['mediaId'], $activity['mediaType']);
        $homeworkResult = $this->getTestpaperService()->getUserLatelyResultByTestId($user['id'], $homework['id'], $activity['fromCourseId'], $activity['id'], $activity['mediaType']);

        if (!$homeworkResult || ($homeworkResult['status'] == 'doing' && !$homeworkResult['updateTime'])) {
            return $this->render('activity/homework/show.html.twig', array(
                'activity' => $activity,
                'homeworkResult' => $homeworkResult,
                'homework' => $homework,
                'courseId' => $activity['fromCourseId'],
            ));
        } elseif ($homeworkResult['status'] == 'finished') {
            return $this->forward('AppBundle:Homework:showResult', array(
                'resultId' => $homeworkResult['id'],
            ));
        }

        return $this->forward('AppBundle:Homework:startDo', array(
            'lessonId' => $activity['id'],
            'homeworkId' => $activity['mediaId'],
        ));
    }

    public function previewAction(Request $request, $task)
    {
        return $this->previewHomework($task['activityId'], $task['courseId']);
    }

    protected function previewHomework($id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id);
        $homeworkctivity = $this->getHomeworkActivityService()->get($activity['mediaId']);
        $assessment = $this->getAssessmentService()->showAssessment($homeworkctivity['assessmentId']);

        return $this->render('activity/homework/preview.html.twig', array(
            'assessment' => $assessment,
        ));
    }

    public function editAction(Request $request, $id, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $activity = $this->getActivityService()->getActivity($id);

        $homeworkActivity = $this->getTestpaperService()->getTestpaperByIdAndType($activity['mediaId'], $activity['mediaType']);

        $questionItems = $this->getTestpaperService()->searchItems(
            array('testId' => $activity['mediaId']),
            array('id' => 'DESC'),
            0,
            PHP_INT_MAX
        );

        $questions = $this->getQuestionService()->findQuestionsByIds(ArrayToolkit::column($questionItems, 'questionId'));

        return $this->render('activity/homework/modal.html.twig', array(
            'activity' => $activity,
            'courseId' => $activity['fromCourseId'],
            'questionItems' => $questionItems,
            'questions' => $questions,
            'courseSetId' => $course['courseSetId'],
            'homework' => $homeworkActivity,
        ));
    }

    public function createAction(Request $request, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        return $this->render('activity/homework/modal.html.twig', array(
            'courseId' => $courseId,
            'courseSetId' => $course['courseSetId'],
        ));
    }

    public function finishConditionAction(Request $request, $activity)
    {
        $homework = $this->getTestpaperService()->getTestpaperByIdAndType($activity['mediaId'], $activity['mediaType']);

        return $this->render('activity/homework/finish-condition.html.twig', array(
            'homework' => $homework,
        ));
    }

    protected function findCourseTestpapers($courseId)
    {
        $conditions = array(
            'courseId' => $courseId,
            'status' => 'open',
        );

        $testpapers = $this->getTestpaperService()->searchTestpapers(
            $conditions,
            array('createdTime' => 'DESC'),
            0,
            PHP_INT_MAX
        );

        return $testpapers;
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return QuestionService
     */
    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }

    /**
     * @return ServiceKernel
     */
    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

    /**
     * @return HomeworkActivityService
     */
    protected function getHomeworkActivityService()
    {
        return $this->getBiz()->service('Activity:HomeworkActivityService');
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->createService('ItemBank:Assessment:AssessmentService');
    }
}
