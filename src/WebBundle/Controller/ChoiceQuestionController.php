<?php

namespace WebBundle\Controller;

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;

class ChoiceQuestionController extends BaseController
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

        return $this->render('WebBundle:ChoiceQuestion:form.html.twig', array(
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

        $parentId       = $request->query->get('parentId', 0);
        $parentQuestion = $this->getQuestionService()->get($parentId);

        $features = array();
        if ($this->container->hasParameter('enabled_features')) {
            $features = $this->container->getParameter('enabled_features');
        }
        $enabledAudioQuestion = in_array('audio_question', $features);

        return $this->render('WebBundle:ChoiceQuestion:form.html.twig', array(
            'course'               => $course,
            'parentQuestion'       => $parentQuestion,
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
