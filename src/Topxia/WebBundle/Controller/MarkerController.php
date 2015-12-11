<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class MarkerController extends BaseController
{
    public function manageAction(Request $request, $id)
    {
        $user   = $this->getCurrentUser();
        $lesson = $this->getCourseService()->getLesson($id);
        return $this->render('TopxiaWebBundle:Marker:index.html.twig', array(
            'lesson' => $lesson
        ));
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

}
