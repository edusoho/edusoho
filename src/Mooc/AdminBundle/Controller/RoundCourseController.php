<?php
namespace Mooc\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class RoundCourseController extends BaseController
{
    public function nextRoundAction(Request $request, $id)
    {
        $this->checkId($id);
        $course = $this->getCourseService()->getCourse($id);
        return $this->render('MoocAdminBundle:Course:next-round.html.twig', array(
            'course' => $course
        ));
    }

    public function roundingAction(Request $request, $id)
    {
        $this->checkId($id);
        $course     = $this->getCourseService()->getCourse($id);
        $conditions = $request->request->all();
        $startTime  = strtotime($conditions['startTime']);
        $endTime    = strtotime($conditions['endTime']);

        if ($startTime < $course['endTime']) {
            return $this->createMessageResponse('info', '周期课程开课时间不得早于上一期课程的结课时间', '周期课程', 3, $this->generateUrl('admin_course'));
        }

        $course['startTime'] = $startTime;
        $course['endTime']   = $endTime;

        $this->getNextRoundService()->rounding($course);

        return $this->redirect($this->generateUrl('admin_course'));
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getNextRoundService()
    {
        return $this->getServiceKernel()->createService('Mooc:Course.NextRoundService');
    }
}
