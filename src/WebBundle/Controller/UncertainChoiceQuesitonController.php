<?php

namespace WebBundle\Controller;

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;

class UncertainChoiceQuesitonController extends BaseController
{
    public function showAction(Request $request, $id, $courseId)
    {
        // TODO: Implement showAction() method.
    }

    public function editAction(Request $request, $courseId, $questionId)
    {
        $course      = $this->getCourseService()->getCourse($courseId);
        $courseTasks = $this->getQuestionService()->findCourseTasks($courseId);
        $question    = $this->getQuestionService()->get($questionId);

        $parentQuestion = array();
        if ($question['parentId'] > 0) {
            $parentQuestion = $this->getQuestionService()->get($question['parentId']);
        }

        return $this->render('WebBundle:UncertainChoiceQuesiton:form.html.twig', array(
            'course'         => $course,
            'question'       => $question,
            'parentQuestion' => $parentQuestion,
            'type'           => $question['type'],
            'courseTasks'    => $courseTasks
        ));
    }

    public function createAction(Request $request, $courseId, $type)
    {
        $course      = $this->getCourseService()->getCourse($courseId);
        $courseTasks = $this->getQuestionService()->findCourseTasks($courseId);

        if ($this->container->hasParameter('enabled_features')) {
            $features = $this->container->getParameter('enabled_features');
        } else {
            $features = array();
        }
        $enabledAudioQuestion = in_array('audio_question', $features);

        return $this->render('WebBundle:UncertainChoiceQuesiton:form.html.twig', array(
            'course'               => $course,
            'parentQuestion'       => null,
            'enabledAudioQuestion' => $enabledAudioQuestion,
            'courseTasks'          => $courseTasks,
            'type'                 => $type
        ));
    }

    private function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
