<?php

namespace WebBundle\Controller;

use Biz\Activity\Service\ActivityService;
use Symfony\Component\HttpFoundation\Request;

class HomeworkActivityController extends BaseController implements ActivityActionInterface
{
    public function showAction(Request $request, $id, $courseId)
    {
    }

    public function editAction(Request $request, $id, $courseId)
    {
        $activity          = $this->getActivityService()->getActivity($id);
        $testpaperActivity = $this->getTestpaperActivityService()->getActivity($activity['mediaId']);

        if ($testpaperActivity) {
            $testpaperActivity['testpaperMediaId'] = $testpaperActivity['mediaId'];
            unset($testpaperActivity['mediaId']);
        }
        $activity = array_merge($activity, $testpaperActivity);

        $testpapers = $this->findCourseTestpapers($activity['fromCourseId']);

        $testpaperNames = array();
        foreach ($testpapers as $testpaper) {
            $testpaperNames[$testpaper['id']] = $testpaper['name'];
        }

        $features = $this->container->hasParameter('enabled_features') ? $this->container->getParameter('enabled_features') : array();

        return $this->render('WebBundle:TestpaperActivity:modal.html.twig', array(
            'activity'       => $activity,
            'testpapers'     => $testpapers,
            'testpaperNames' => $testpaperNames,
            'features'       => $features,
            'courseId'       => $activity['fromCourseId']
        ));
    }

    public function createAction(Request $request, $courseId)
    {
        return $this->render('WebBundle:HomeworkActivity:modal.html.twig', array(
            'courseId' => $courseId
        ));
    }

    protected function findCourseTestpapers($courseId)
    {
        $conditions = array(
            'courseId' => $courseId,
            'status'   => 'open'
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

    protected function getTestpaperActivityService()
    {
        return $this->createService('TestpaperActivity:TestpaperActivityService');
    }
}
