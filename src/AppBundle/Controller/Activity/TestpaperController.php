<?php

namespace AppBundle\Controller\Activity;

use Biz\Course\Service\CourseService;
use Biz\Activity\Service\ActivityService;
use Biz\Testpaper\Service\TestpaperService;
use Symfony\Component\HttpFoundation\Request;
use Biz\Activity\Service\TestpaperActivityService;
use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;

class TestpaperController extends BaseActivityController implements ActivityActionInterface
{
    public function showAction(Request $request, $activity, $preview = 0)
    {
        if ($preview) {
            return $this->previewTestpaper($activity['id'], $activity['fromCourseId']);
        }

        $user = $this->getUser();
        $testpaperActivity = $this->getTestpaperActivityService()->getActivity($activity['mediaId']);
        $testpaper = $this->getTestpaperService()->getTestpaperByIdAndType($testpaperActivity['mediaId'], $activity['mediaType']);

        if (!$testpaper) {
            return $this->render('activity/testpaper/preview.html.twig', array(
                'paper' => null,
            ));
        }

        $testpaperResult = $this->getTestpaperService()->getUserLatelyResultByTestId($user['id'], $testpaperActivity['mediaId'], $activity['fromCourseId'], $activity['id'], $activity['mediaType']);

        if (!$testpaperResult || ('doing' == $testpaperResult['status'] && !$testpaperResult['updateTime']) || 'open' != $testpaper['status']) {
            return $this->render('activity/testpaper/show.html.twig', array(
                'activity' => $activity,
                'testpaperActivity' => $testpaperActivity,
                'testpaperResult' => $testpaperResult,
                'testpaper' => $testpaper,
                'courseId' => $activity['fromCourseId'],
            ));
        } elseif ('finished' === $testpaperResult['status']) {
            return $this->forward('AppBundle:Testpaper/Testpaper:showResult', array(
                'resultId' => $testpaperResult['id'],
            ));
        }

        return $this->forward('AppBundle:Testpaper/Testpaper:doTestpaper', array(
            'testId' => $testpaperActivity['mediaId'],
            'lessonId' => $activity['id'],
        ));
    }

    public function previewAction(Request $request, $task)
    {
        return $this->previewTestpaper($task['activityId'], $task['courseId']);
    }

    public function previewTestpaper($id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id);
        $testpaperActivity = $this->getTestpaperActivityService()->getActivity($activity['mediaId']);
        $testpaper = $this->getTestpaperService()->getTestpaperByIdAndType($testpaperActivity['mediaId'], $activity['mediaType']);

        if (!$testpaper) {
            return $this->render('activity/testpaper/preview.html.twig', array(
                'paper' => null,
            ));
        }

        $questions = $this->getTestpaperService()->showTestpaperItems($testpaper['id']);

        $total = $this->getTestpaperService()->countQuestionTypes($testpaper, $questions);

        $attachments = $this->getTestpaperService()->findAttachments($testpaper['id']);

        return $this->render('activity/testpaper/preview.html.twig', array(
            'questions' => $questions,
            'limitedTime' => $testpaperActivity['limitedTime'],
            'paper' => $testpaper,
            'paperResult' => array(),
            'total' => $total,
            'attachments' => $attachments,
            'questionTypes' => $this->getTestpaperService()->getCheckedQuestionTypeBySeq($testpaper),
        ));
    }

    public function editAction(Request $request, $id, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        $activity = $this->getActivityService()->getActivity($id);
        $testpaperActivity = $this->getTestpaperActivityService()->getActivity($activity['mediaId']);

        if ($testpaperActivity) {
            $testpaperActivity['testpaperMediaId'] = $testpaperActivity['mediaId'];
            unset($testpaperActivity['mediaId']);
        }
        $activity = array_merge($activity, $testpaperActivity);

        $testpapers = $this->findCourseTestpapers($course);

        $features = $this->container->hasParameter('enabled_features') ? $this->container->getParameter('enabled_features') : array();

        return $this->render('activity/testpaper/modal.html.twig', array(
            'activity' => $activity,
            'testpapers' => $testpapers,
            'features' => $features,
            'courseId' => $activity['fromCourseId'],
            'course' => $course,
        ));
    }

    public function createAction(Request $request, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $testpapers = $this->findCourseTestpapers($course);

        $features = $this->container->hasParameter('enabled_features') ? $this->container->getParameter('enabled_features') : array();

        return $this->render('activity/testpaper/modal.html.twig', array(
            'testpapers' => $testpapers,
            'features' => $features,
            'course' => $course,
        ));
    }

    public function finishConditionAction(Request $request, $activity)
    {
        $testpaperActivity = $this->getTestpaperActivityService()->getActivity($activity['mediaId']);

        return $this->render('activity/testpaper/finish-condition.html.twig', array(
            'testpaperActivity' => $testpaperActivity,
        ));
    }

    public function learnDataDetailAction(Request $request, $task)
    {
        $activity = $this->getActivityService()->getActivity($task['activityId'], true);
        $testpaper = $this->getTestpaperService()->getTestpaperByIdAndType($activity['ext']['mediaId'], $activity['mediaType']);

        $conditions = array(
            'courseTaskId' => $task['id'],
        );

        $paginator = new Paginator(
            $request,
            $this->getTaskResultService()->countTaskResults($conditions),
            20
        );

        $taskResults = $this->getTaskResultService()->searchTaskResults(
            $conditions,
            array('createdTime' => 'ASC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = ArrayToolkit::column($taskResults, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);
        $testpaperResults = $this->getTestpaperService()->findTestResultsByTestpaperIdAndUserIds($userIds, $testpaper['id']);

        return $this->render('activity/testpaper/learn-data-detail-modal.html.twig', array(
            'task' => $task,
            'taskResults' => $taskResults,
            'users' => $users,
            'testpaperResults' => $testpaperResults,
            'paginator' => $paginator,
        ));
    }

    protected function findCourseTestpapers($course)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $conditions = array(
            'courseSetId' => $course['courseSetId'],
            'status' => 'open',
            'type' => 'testpaper',
        );

        if ($courseSet['parentId'] > 0 && $courseSet['locked']) {
            $conditions['copyIdGT'] = 0;
        }

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

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return TestpaperActivityService
     */
    protected function getTestpaperActivityService()
    {
        return $this->createService('Activity:TestpaperActivityService');
    }
}
