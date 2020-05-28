<?php

namespace AppBundle\Controller\Activity;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\HomeworkActivityService;
use Biz\Course\Service\CourseService;
use Biz\Question\Service\QuestionService;
use Biz\Testpaper\Service\TestpaperService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;

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

        if (!$homeworkResult || ('doing' == $homeworkResult['status'] && !$homeworkResult['updateTime'])) {
            return $this->render('activity/homework/show.html.twig', [
                'activity' => $activity,
                'homeworkResult' => $homeworkResult,
                'homework' => $homework,
                'courseId' => $activity['fromCourseId'],
            ]);
        } elseif ('finished' == $homeworkResult['status']) {
            return $this->forward('AppBundle:Homework:showResult', [
                'resultId' => $homeworkResult['id'],
            ]);
        }

        return $this->forward('AppBundle:Homework:startDo', [
            'lessonId' => $activity['id'],
            'homeworkId' => $activity['mediaId'],
        ]);
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

        return $this->render('activity/homework/preview.html.twig', [
            'assessment' => $assessment,
        ]);
    }

    public function editAction(Request $request, $id, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $activity = $this->getActivityService()->getActivity($id);

        $homeworkActivity = $this->getTestpaperService()->getTestpaperByIdAndType($activity['mediaId'], $activity['mediaType']);

        $questionItems = $this->getTestpaperService()->searchItems(
            ['testId' => $activity['mediaId']],
            ['id' => 'DESC'],
            0,
            PHP_INT_MAX
        );

        $questions = $this->getQuestionService()->findQuestionsByIds(ArrayToolkit::column($questionItems, 'questionId'));

        return $this->render('activity/homework/modal.html.twig', [
            'activity' => $activity,
            'courseId' => $activity['fromCourseId'],
            'questionItems' => $questionItems,
            'questions' => $questions,
            'courseSetId' => $course['courseSetId'],
            'homework' => $homeworkActivity,
        ]);
    }

    public function createAction(Request $request, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        return $this->render('activity/homework/modal.html.twig', [
            'courseId' => $courseId,
            'courseSetId' => $course['courseSetId'],
        ]);
    }

    public function finishConditionAction(Request $request, $activity)
    {
        $homework = $this->getTestpaperService()->getTestpaperByIdAndType($activity['mediaId'], $activity['mediaType']);

        return $this->render('activity/homework/finish-condition.html.twig', [
            'homework' => $homework,
        ]);
    }

    protected function findCourseTestpapers($courseId)
    {
        $conditions = [
            'courseId' => $courseId,
            'status' => 'open',
        ];

        $testpapers = $this->getTestpaperService()->searchTestpapers(
            $conditions,
            ['createdTime' => 'DESC'],
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
