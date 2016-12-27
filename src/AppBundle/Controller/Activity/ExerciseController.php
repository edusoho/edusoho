<?php

namespace AppBundle\Controller\Activity;

use AppBundle\Controller\BaseController;
use Biz\Activity\Service\ActivityService;
use Symfony\Component\HttpFoundation\Request;

class ExerciseController extends BaseController implements ActivityActionInterface
{
    public function showAction(Request $request, $id, $courseId)
    {
        $user = $this->getUser();

        $activity       = $this->getActivityService()->getActivity($id);
        $exercise       = $this->getTestpaperService()->getTestpaper($activity['mediaId']);
        $exerciseResult = $this->getTestpaperService()->getUserLatelyResultByTestId($user['id'], $exercise['id'], $activity['fromCourseSetId'], $activity['id'], $activity['mediaType']);

        if (!$exerciseResult || ($exerciseResult['status'] == 'doing' && !$exerciseResult['updateTime'])) {
            return $this->render('activity/exercise/show.html.twig', array(
                'activity'       => $activity,
                'exerciseResult' => $exerciseResult,
                'exercise'       => $exercise,
                'courseId'       => $activity['fromCourseId']
            ));
        }

        return $this->forward('AppBundle:Exercise:startDo', array(
            'lessonId'   => $activity['id'],
            'exerciseId' => $activity['mediaId']
        ));
    }

    public function editAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id);
        $exercise = $this->getTestpaperService()->getTestpaper($activity['mediaId']);

        $activity = array_merge($activity, $exercise);

        $questionTypes = $this->get('codeages_plugin.dict_twig_extension')->getDict('questionType');

        return $this->render('activity/exercise/modal.html.twig', array(
            'questionTypes' => $questionTypes,
            'activity'      => $activity,
            'courseId'      => $activity['fromCourseId']
        ));
    }

    public function createAction(Request $request, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        $questionTypes = $this->get('codeages_plugin.dict_twig_extension')->getDict('questionType');


        return $this->render('activity/exercise/modal.html.twig', array(
            'courseId'      => $courseId,
            'questionTypes' => $questionTypes,
            'courseSetId'   => $course['courseSetId']
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

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getTestpaperActivityService()
    {
        return $this->createService('Activity:TestpaperActivityService');
    }
}
