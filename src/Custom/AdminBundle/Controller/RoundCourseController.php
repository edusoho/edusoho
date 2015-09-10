<?php
/**
 * Created by PhpStorm.
 * User: retamia
 * Date: 15/9/9
 * Time: 15:44
 */

namespace Custom\AdminBundle\Controller;


use Symfony\Component\HttpFoundation\Request;

class RoundCourseController extends BaseController
{
    public function nextRoundAction(Request $request, $id)
    {
        $this->checkId($id);
        $course = $this->getCourseService()->getCourse($id);
        return $this->render('TopxiaAdminBundle:Course:next-round.html.twig', array(
            'course' => $course,
        ));
    }

    public function roundingAction(Request $request, $id)
    {
        $this->checkId($id);
        $course = $this->getCourseService()->getCourse($id);

        $conditions = $request->request->all();
        $course['startTime'] = strtotime($conditions['startTime']);
        $course['endTime'] = strtotime($conditions['endTime']);

        $this->getNextRoundService()->rounding($course);

        return $this->redirect($this->generateUrl('admin_course'));
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getNextRoundService()
    {
        return $this->getServiceKernel()->createService('Custom:Course.NextRoundService');
    }
}