<?php

namespace AppBundle\Controller\Activity;

use Topxia\Common\ArrayToolkit;
use AppBundle\Controller\BaseController;
use Topxia\Service\Common\ServiceKernel;
use Biz\Activity\Service\ActivityService;
use Symfony\Component\HttpFoundation\Request;

class HomeworkController extends BaseController implements ActivityActionInterface
{
    public function showAction(Request $request, $id, $courseId)
    {
        $user = $this->getUser();

        $activity       = $this->getActivityService()->getActivity($id);
        $homework       = $this->getTestpaperService()->getTestpaper($activity['mediaId']);
        $homeworkResult = $this->getTestpaperService()->getUserLatelyResultByTestId($user['id'], $homework['id'], $activity['fromCourseSetId'], $activity['id'], $activity['mediaType']);

        if (!$homeworkResult || ($homeworkResult['status'] == 'doing' && !$homeworkResult['updateTime'])) {
            return $this->render('activity/homework/show.html.twig', array(
                'activity'       => $activity,
                'homeworkResult' => $homeworkResult,
                'homework'       => $homework,
                'courseId'       => $activity['fromCourseId']
            ));
        }

        return $this->forward('AppBundle:Homework:startDo', array(
            'lessonId'   => $activity['id'],
            'homeworkId' => $activity['mediaId']
        ));
    }

    public function editAction(Request $request, $id, $courseId)
    {
        $course   = $this->getCourseService()->getCourse($courseId);
        $activity = $this->getActivityService()->getActivity($id);

        $homeworkActivity = $this->getTestpaperService()->getTestpaper($activity['mediaId']);

        $activity = array_merge($activity, $homeworkActivity);

        $questionItems = $this->getTestpaperService()->searchItems(
            array('testId' => $activity['mediaId']),
            array('id' => 'DESC'),
            0, PHP_INT_MAX
        );

        $questions = $this->getQuestionService()->findQuestionsByIds(ArrayToolkit::column($questionItems, 'questionId'));

        return $this->render('activity/homework/modal.html.twig', array(
            'activity'      => $activity,
            'courseId'      => $activity['fromCourseId'],
            'questionItems' => $questionItems,
            'questions'     => $questions,
            'courseSetId'   => $course['courseSetId']
        ));
    }

    public function createAction(Request $request, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        return $this->render('activity/homework/modal.html.twig', array(
            'courseId'    => $courseId,
            'courseSetId' => $course['courseSetId']
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

    protected function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question:QuestionService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
