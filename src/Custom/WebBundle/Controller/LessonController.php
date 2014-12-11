<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;
use Topxia\Service\Course\CourseService;
use Topxia\Common\ArrayToolkit;

class LessonController extends BaseController
{
    public function learnShowAction($lessonId)
    {
        $learns = $this->getCourseService()->findLearnsByLessonId($lessonId);
        $learners = $this->getUserService()->findUsersByIds(ArrayToolkit::column($learns, 'userId'));

        return $this->render(
        'CustomWebBundle:CourseLesson:lesson-learner-show.html.twig', array(
            'learners' => $learners
        ));
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}