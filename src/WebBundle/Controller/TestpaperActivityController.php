<?php

namespace WebBundle\Controller;

use Biz\Activity\Service\ActivityService;
use Symfony\Component\HttpFoundation\Request;

class TestpaperActivityController extends BaseController implements ActivityActionInterface
{
    public function showAction(Request $request, $id, $courseId)
    {
        $user              = $this->getUser();
        $activity          = $this->getActivityService()->getActivity($id);
        $testpaperActivity = $this->getTestpaperActivityService()->getActivity($activity['mediaId']);
        $testpaper         = $this->getTestpaperService()->getTestpaper($testpaperActivity['mediaId']);

        $testpaperResult = $this->getTestpaperService()->getUserLatelyResultByTestId($user['id'], $testpaperActivity['mediaId'], $activity['fromCourseSetId'], $activity['id'], $activity['mediaType']);

        if (!$testpaperResult || ($testpaperResult['status'] == 'doing' && !$testpaperResult['updateTime'])) {
            return $this->render('WebBundle:TestpaperActivity:show.html.twig', array(
                'activity'          => $activity,
                'testpaperActivity' => $testpaperActivity,
                'testpaperResult'   => $testpaperResult,
                'testpaper'         => $testpaper,
                'courseId'          => $activity['fromCourseId']
            ));
        }

        return $this->forward('WebBundle:Testpaper:doTestpaper', array(
            'testId'   => $testpaperActivity['mediaId'],
            'lessonId' => $activity['id']
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

        return $this->render('WebBundle:TestpaperActivity:modal.html.twig', array(
            'activity'    => $activity,
            'testpapers'  => $testpapers,
            'features'    => $features,
            'courseId'    => $activity['fromCourseId'],
            'courseSetId' => $course['courseSetId']
        ));
    }

    public function createAction(Request $request, $courseId)
    {
        $course     = $this->getCourseService()->getCourse($courseId);
        $testpapers = $this->findCourseTestpapers($course['courseSetId']);

        $features = $this->container->hasParameter('enabled_features') ? $this->container->getParameter('enabled_features') : array();

        return $this->render('WebBundle:TestpaperActivity:modal.html.twig', array(
            'testpapers'  => $testpapers,
            'features'    => $features,
            'courseSetId' => $course['courseSetId']
        ));
    }

    protected function findCourseTestpapers($courseId)
    {
        $conditions = array(
            'courseId' => $courseId,
            'status'   => 'open',
            'type'     => 'testpaper'
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
        return $this->createService('TestpaperActivity:TestpaperActivityService');
    }
}
