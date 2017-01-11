<?php

namespace AppBundle\Controller\Activity;

use AppBundle\Controller\BaseController;
use Biz\Activity\Service\ActivityService;
use Symfony\Component\HttpFoundation\Request;

class TestpaperController extends BaseController implements ActivityActionInterface
{
    public function showAction(Request $request, $id, $courseId, $preview = 0)
    {
        if ($preview) {
            return $this->forward('AppBundle:Activity/Testpaper:preview', array(
                'id'       => $id,
                'courseId' => $courseId
            ));
        }

        $user              = $this->getUser();
        $activity          = $this->getActivityService()->getActivity($id);
        $testpaperActivity = $this->getTestpaperActivityService()->getActivity($activity['mediaId']);
        $testpaper         = $this->getTestpaperService()->getTestpaper($testpaperActivity['mediaId']);

        $testpaperResult = $this->getTestpaperService()->getUserLatelyResultByTestId($user['id'], $testpaperActivity['mediaId'], $activity['fromCourseSetId'], $activity['id'], $activity['mediaType']);

        if (!$testpaperResult || ($testpaperResult['status'] == 'doing' && !$testpaperResult['updateTime'])) {
            return $this->render('activity/testpaper/show.html.twig', array(
                'activity'          => $activity,
                'testpaperActivity' => $testpaperActivity,
                'testpaperResult'   => $testpaperResult,
                'testpaper'         => $testpaper,
                'courseId'          => $activity['fromCourseId']
            ));
        } elseif ($testpaperResult['status'] == 'finished') {
            return $this->forward('AppBundle:Testpaper/Testpaper:showResult', array(
                'resultId' => $testpaperResult['id']
            ));
        }

        return $this->forward('AppBundle:Testpaper/Testpaper:doTestpaper', array(
            'testId'   => $testpaperActivity['mediaId'],
            'lessonId' => $activity['id']
        ));
    }

    public function previewAction(Request $request, $id, $courseId)
    {
        $activity          = $this->getActivityService()->getActivity($id);
        $testpaperActivity = $this->getTestpaperActivityService()->getActivity($activity['mediaId']);
        $testpaper         = $this->getTestpaperService()->getTestpaper($testpaperActivity['mediaId']);

        if (!$testpaper) {
            return $this->createMessageResponse('error', 'testpaper not found');
        }

        $questions = $this->getTestpaperService()->showTestpaperItems($testpaper['id']);

        $total = $this->getTestpaperService()->countQuestionTypes($testpaper, $questions);

        $attachments = $this->getTestpaperService()->findAttachments($testpaper['id']);

        return $this->render('activity/testpaper/preview.html.twig', array(
            'questions'     => $questions,
            'limitedTime'   => $testpaperActivity['limitedTime'],
            'paper'         => $testpaper,
            'paperResult'   => array(),
            'total'         => $total,
            'attachments'   => $attachments,
            'questionTypes' => $this->getCheckedQuestionType($testpaper)
        ));
    }

    public function editAction(Request $request, $id, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        $activity          = $this->getActivityService()->getActivity($id);
        $testpaperActivity = $this->getTestpaperActivityService()->getActivity($activity['mediaId']);

        if ($testpaperActivity) {
            $testpaperActivity['testpaperMediaId'] = $testpaperActivity['mediaId'];
            unset($testpaperActivity['mediaId']);
        }
        $activity = array_merge($activity, $testpaperActivity);

        $testpapers = $this->findCourseTestpapers($course['courseSetId']);

        $features = $this->container->hasParameter('enabled_features') ? $this->container->getParameter('enabled_features') : array();

        return $this->render('activity/testpaper/modal.html.twig', array(
            'activity'   => $activity,
            'testpapers' => $testpapers,
            'features'   => $features,
            'courseId'   => $activity['fromCourseId'],
            'course'     => $course
        ));
    }

    public function createAction(Request $request, $courseId)
    {
        $course     = $this->getCourseService()->getCourse($courseId);
        $testpapers = $this->findCourseTestpapers($course['courseSetId']);

        $features = $this->container->hasParameter('enabled_features') ? $this->container->getParameter('enabled_features') : array();

        return $this->render('activity/testpaper/modal.html.twig', array(
            'testpapers' => $testpapers,
            'features'   => $features,
            'course'     => $course
        ));
    }

    public function finishConditionAction($activity)
    {
        $testpaperActivity = $this->getTestpaperActivityService()->getActivity($activity['mediaId']);

        return $this->render('activity/testpaper/finish-condition.html.twig', array(
            'testpaperActivity' => $testpaperActivity
        ));
    }

    protected function findCourseTestpapers($courseId)
    {
        $conditions = array(
            'courseSetId' => $courseId,
            'status'      => 'open',
            'type'        => 'testpaper'
        );

        $testpapers = $this->getTestpaperService()->searchTestpapers(
            $conditions,
            array('createdTime' => 'DESC'),
            0,
            PHP_INT_MAX
        );

        return $testpapers;
    }

    protected function getCheckedQuestionType($testpaper)
    {
        $questionTypes = array();
        foreach ($testpaper['metas']['counts'] as $type => $count) {
            if ($count > 0) {
                $questionTypes[] = $type;
            }
        }

        return $questionTypes;
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getTestpaperActivityService()
    {
        return $this->createService('Activity:TestpaperActivityService');
    }
}
