<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class AnalysisController extends BaseController
{

    public function registerUserAction(Request $request)
    {
        $dateType = $request->query->get('dateType');

        $map = array();
        $students = $this->getCourseService()->searchMember(array('date'=>$dateType, 'role'=>'student'), 0 , 10000);
        foreach ($students as $student) {
            if (empty($map[$student['courseId']])) {
                $map[$student['courseId']] = 1;
            } else {
                $map[$student['courseId']] ++;
            }
        }
        asort($map, SORT_NUMERIC);
        $map = array_slice($map, 0, 5, true);

        $courses = array();
        foreach ($map as $courseId => $studentNum) {
            $course = $this->getCourseService()->getCourse($courseId);
            $course['addedStudentNum'] = $studentNum;
            $course['addedMoney'] = 0;

            $orders = $this->getOrderService()->searchOrders(array('courseId'=>$courseId, 'status' => 'paid', 'date'=>$dateType), 'latest', 0, 10000);
            foreach ($orders as $id => $order) {
                $course['addedMoney'] += $order['price'];
            }

            $courses[] = $course;
        }

        return $this->render('TopxiaAdminBundle:Default:popular-courses-table.html.twig', array(
            'courses' => $courses
        ));
        
    }

   

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Course.ThreadService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }

    protected function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }
}
